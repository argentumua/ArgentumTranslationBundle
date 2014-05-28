<?php

namespace Argentum\TranslationBundle\Command;

use Argentum\TranslationBundle\Entity\TranslationDomain;
use Argentum\TranslationBundle\Entity\TranslationDomainRepository;
use Argentum\TranslationBundle\Entity\TranslationDomainTranslation;
use Argentum\TranslationBundle\Entity\TranslationUnitRepository;
use Argentum\TranslationBundle\Entity\TranslationUnitTranslation;
use Argentum\TranslationBundle\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Imports translations from files located in specified directory or bundle(s)
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationImportCommand extends ContainerAwareCommand
{
    private $locales;
    private $domains;
    private $dirs;
    private $messages;
    private $transLocales;
    private $baseDir;
    private $domainObjects;
    private $totalDomainsCreated;
    private $totalTransCreated;
    private $totalTransUpdated;
    private $localTransCreated;
    private $localTransUpdated;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var TranslationLoader
     */
    private $loader;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TranslationDomainRepository
     */
    protected $domainRepository;

    /**
     * @var TranslationUnitRepository
     */
    private $unitRepository;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('translation:import')
            ->setDescription('Imports translations from files to database')
            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Source directory')
            ->addOption('locales', 'l', InputOption::VALUE_OPTIONAL, 'Comma-separated list of locales')
            ->addOption('bundles', 'b', InputOption::VALUE_OPTIONAL, 'Comma-separated list of bundles')
            ->addArgument('domain', InputArgument::IS_ARRAY, 'Domain to import')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command imports translation strings from bundles
or specified directory. If directory is specified translations will be loaded
only from this directory. Otherwise, all bundles (or specified with --bundles
option) will be scanned for translations and than loaded to database. If no
bundles are specified entire project including Form and Validation components
will be scanned.
While importing all modified existing translations will be overwritten, new
translations will be appended to existing domains, and new domains will be
created.

Import all translations from the directory:
<info>php %command.full_name% --dir=/path/to/translations</info>

Import translations from the directory only for the domain "messages":
<info>php %command.full_name% --dir=/path/to/translations messages</info>

Import translations from the directory only for domain "messages" and locales "ru" and "uk":
<info>php %command.full_name% --dir=/path/to/translations --locales="ru,uk" messages</info>

Import translations from the specified bundles:
<info>php %command.full_name% --bundles="SonataAdminBundle, SonataUserBundle"</info>

Import translations from the entire project including all bundles, Form and Validation
components, and local application translations:
<info>php %command.full_name%</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->translator = $this->getContainer()->get('translator');
        $this->loader = $this->getContainer()->get('translation.loader');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->domainRepository = $this->em->getRepository('ArgentumTranslationBundle:TranslationDomain');
        $this->unitRepository = $this->em->getRepository('ArgentumTranslationBundle:TranslationUnit');
        $this->transLocales = $this->getContainer()->getParameter('argentum_translation.locales');
        $this->baseDir = preg_replace('@[^/]+$@', '', $this->getContainer()->getParameter('kernel.root_dir'));
        $this->totalDomainsCreated = 0;
        $this->totalTransCreated = 0;
        $this->totalTransUpdated = 0;
        $this->messages = array();
        $this->domainObjects = array();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateInputParameters($input);

        foreach ($this->dirs as $dir) {
            $relativeDir = str_replace($this->baseDir, '', $dir);

            foreach ($this->locales as $locale) {
                $catalogue = new MessageCatalogue($locale);
                $this->loader->loadMessages($dir, $catalogue);

                $domains = $catalogue->getDomains();
                if ($this->domains) {
                    $domains = array_intersect($domains, $this->domains);
                }

                foreach ($domains as $domain) {
                    $output->write(
                        sprintf('Importing %s/<comment>%s.%s</comment>:', $relativeDir, $domain, $locale)
                    );

                    $this->loadDatabaseMessages($domain, $locale);
                    $messages = array_diff_assoc($catalogue->all($domain), $this->messages[$domain][$locale]);

                    $this->localTransCreated = 0;
                    $this->localTransUpdated = 0;

                    foreach ($messages as $token => $content) {
                        $this->addMessage($domain, $locale, $token, $content);
                    }

                    $this->totalTransCreated += $this->localTransCreated;
                    $this->totalTransUpdated += $this->localTransUpdated;

                    $output->writeln(
                        sprintf(' %d created, %d updated', $this->localTransCreated, $this->localTransUpdated)
                    );
                }
            }
        }

        $output->writeln(
            sprintf(
                '<info>Import completed. Total <comment>%d</comment> domains created, '
                . '<comment>%d</comment> translations created, <comment>%d</comment> updated</info>',
                $this->totalDomainsCreated,
                $this->totalTransCreated,
                $this->totalTransUpdated
            )
        );
    }

    /**
     * Validates input parameters.
     *
     * @param InputInterface $input
     *
     * @throws \InvalidArgumentException
     */
    protected function validateInputParameters(InputInterface $input)
    {
        $this->dirs = array();

        // locales
        $this->locales = $this->translator->getLocales();
        if ($locales = $input->getOption('locales')) {
            $locales = array_map('trim', explode(',', $locales));

            if ($diff = array_diff($locales, $this->locales)) {
                throw new \InvalidArgumentException(
                    sprintf('The following locales do not exist: %s', implode(', ', $diff))
                );
            }

            $this->locales = $locales;
        }

        // domains
        $this->domains = array();
        if ($domains = $input->getArgument('domain')) {
            $this->domains = $domains;
        }

        // dir
        if ($dir = $input->getOption('dir')) {
            if (!is_dir($dir)) {
                throw new \InvalidArgumentException(
                    sprintf('The source directory "%s" does not exist', $dir)
                );
            }

            $this->dirs[] = $dir;
        } else {
            $bundles = $input->getOption('bundles');
            $bundles = $bundles ? array_map('trim', explode(',', $bundles)) : array();
            $bundlesAll = array_keys($this->getContainer()->getParameter('kernel.bundles'));

            if ($diff = array_diff($bundles, $bundlesAll)) {
                throw new \InvalidArgumentException(
                    sprintf('The following bundles do not exist: %s', implode(', ', $diff))
                );
            }

            if ($all = count($bundles) == 0) {
                $bundles = $bundlesAll;
            }

            $this->collectTranslationsDirectories($bundles, $all);
        }
    }

    /**
     * Collects translations directories.
     *
     * @param array $bundles
     * @param boolean $all
     */
    protected function collectTranslationsDirectories($bundles, $all)
    {
        /** @var Application $app */
        $app = $this->getApplication();
        $translationsPath = '%s/Resources/translations';
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $overridePath = $rootDir . '/Resources/%s/translations';

        if ($all) {
            foreach (array('Symfony\Component\Validator\Validator', 'Symfony\Component\Form\Form') as $class) {
                if (class_exists($class)) {
                    $r = new \ReflectionClass($class);
                    $this->dirs[] = sprintf($translationsPath, dirname($r->getFilename()));
                }
            }
        }

        foreach ($bundles as $bundleName) {
            $bundle = $app->getKernel()->getBundle($bundleName);
            if (is_dir($dir = sprintf($translationsPath, $bundle->getPath()))) {
                $this->dirs[] = $dir;
            }
            if (is_dir($dir = sprintf($overridePath, $bundleName))) {
                $this->dirs[] = $dir;
            }
        }

        if ($all && is_dir($dir = sprintf($translationsPath, $rootDir))) {
            $this->dirs[] = $dir;
        }

        $this->dirs = array_unique($this->dirs);
    }

    /**
     * Loads database messages for given domain and locale.
     *
     * @param string $domain
     * @param string $locale
     */
    protected function loadDatabaseMessages($domain, $locale)
    {
        if (!isset($this->messages[$domain]) || !isset($this->messages[$domain][$locale])) {
            $messages = $this->unitRepository->getMessagesForLocaleAndDomain($locale, $domain);

            if (!isset($this->messages[$domain])) {
                $this->messages[$domain] = array();
            }
            if (!isset($this->messages[$domain][$locale])) {
                $this->messages[$domain][$locale] = array();
            }

            $this->messages[$domain][$locale] = array_merge($this->messages[$domain][$locale], $messages);
        }
    }

    /**
     * Returns domain or creates a new one.
     *
     * @param string $domain
     *
     * @return TranslationDomain
     */
    protected function getDomain($domain)
    {
        if (!isset($this->domainObjects[$domain])) {
            $this->domainObjects[$domain] = $this->domainRepository->getByName($domain);
        }

        if (is_null($this->domainObjects[$domain])) {
            $this->messages[$domain] = array();

            $domainObject = new TranslationDomain();
            $domainObject->setName($domain);
            foreach ($this->transLocales as $transLocale) {
                $this->messages[$domain][$transLocale] = array();
                /** @var TranslationDomainTranslation $domainTranslation */
                $domainTranslation = $domainObject->translate($transLocale);
                $domainTranslation->setTitle($domain);
            }
            $this->em->persist($domainObject);
            $domainObject->mergeNewTranslations();
            $this->em->flush();

            $this->domainObjects[$domain] = $domainObject;

            $this->totalDomainsCreated++;
        }

        return $this->domainObjects[$domain];
    }

    /**
     * Adds message to database.
     *
     * @param string $domain
     * @param string $locale
     * @param string $token
     * @param string $content
     */
    protected function addMessage($domain, $locale, $token, $content)
    {
        $domainObject = $this->getDomain($domain);

        $unit = $this->unitRepository->findOrCreateByTokenAndDomain($token, $domainObject);
        $isNew = !$unit->getId();
        if ($isNew) {
            $this->localTransCreated++;
        }

        /** @var TranslationUnitTranslation $unitTranslation */
        $unitTranslation = $unit->translate($locale);
        if ($unitTranslation->getContent() != $content && !$isNew) {
            $this->localTransUpdated++;
        }
        $unitTranslation->setContent($content);

        $this->em->persist($unit);
        $unit->mergeNewTranslations();
        $this->em->flush();
    }
}
