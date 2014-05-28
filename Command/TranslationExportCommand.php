<?php

namespace Argentum\TranslationBundle\Command;

use Argentum\TranslationBundle\Translation\DatabaseLoader;
use Argentum\TranslationBundle\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Writer\TranslationWriter;

/**
 * Exports translations from database to files
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationExportCommand extends ContainerAwareCommand
{
    private $locales;
    private $domains;
    private $targetDir;
    private $format;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var DatabaseLoader
     */
    private $loader;

    /**
     * @var TranslationWriter
     */
    private $writer;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('translation:export')
            ->setDescription('Exports translations from the database to files')
            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'Target directory', '.')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format', 'yml')
            ->addOption('locales', 'l', InputOption::VALUE_OPTIONAL, 'Comma-separated list of locales')
            ->addArgument('domain', InputArgument::IS_ARRAY, 'Domain to export')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command exports translation from the database
to files for specified domains and locales in specified format.

Export all translations to the directory:
<info>php %command.full_name% --dir=/path/to/translations</info>

Export translations for domain "messages":
<info>php %command.full_name% --dir=/path/to/translations messages</info>

Export translations for domain "messages" and locales "ru" and "uk" in xliff format:
<info>php %command.full_name% --dir=/path/to/translations --locales="ru,uk" --format=xlf messages</info>
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
        $this->loader = $this->getContainer()->get('argentum.translation.loader.database');
        $this->writer = $this->getContainer()->get('translation.writer');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateInputParameters($input);

        foreach ($this->domains as $domain) {
            $output->write(sprintf('Exporting %s:', $domain));

            foreach ($this->locales as $locale) {
                $catalogue = $this->loader->load(null, $locale, $domain);

                $this->writer->writeTranslations($catalogue, $this->format, array('path' => $this->targetDir));

                $output->write(sprintf(' <comment>%s</comment>', $locale));
            }

            $output->writeln('');
        }

        $output->writeln('<info>All done</info>');
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
        $this->domains = $this->translator->getDomains();
        if ($domains = $input->getArgument('domain')) {
            if ($diff = array_diff($domains, $this->domains)) {
                throw new \InvalidArgumentException(
                    sprintf('The following domains do not exist: %s', implode(', ', $diff))
                );
            }

            $this->domains = $domains;
        }

        // dir
        $this->targetDir = $input->getOption('dir');
        if (!is_dir($this->targetDir)) {
            throw new \InvalidArgumentException(
                sprintf('The target directory "%s" does not exist', $this->targetDir)
            );
        }

        // format
        $this->format = $input->getOption('format');
        $formats = $this->writer->getFormats();
        if (!in_array($this->format, $formats)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid output format "%s". Supported formats: %s', $this->format, implode(', ', $formats))
            );
        }
    }
}
