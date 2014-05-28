<?php

namespace Argentum\TranslationBundle\Translation;

use Argentum\TranslationBundle\Entity\TranslationDomainRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Translator.
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class Translator extends BaseTranslator
{
    private $locales;

    /**
     * Adds all database resources.
     */
    public function addDatabaseResources()
    {
        $resources = array();
        $file = sprintf('%s/database.resources.php', $this->options['cache_dir']);
        $cache = new ConfigCache($file, $this->options['debug']);

        if (!$cache->isFresh()) {
            $domains = $this->getDomains();

            foreach ($this->locales as $locale) {
                foreach ($domains as $domain) {
                    $resources[] = array(
                        'locale' => $locale,
                        'domain' => $domain
                    );
                }
            }

            $metadata = array();
            foreach ($resources as $resource) {
                $metadata[] = new DatabaseResource($resource['locale'], $resource['domain']);
            }

            $content = sprintf("<?php return %s;", var_export($resources, true));
            $cache->write($content, $metadata);
        } else {
            $resources = include($file);
        }

        foreach ($resources as $resource) {
            $this->addResource('db', 'database', $resource['locale'], $resource['domain']);
        }
    }

    /**
     * Clears the entire cache.
     */
    public function clearCache()
    {
        $finder = new Finder();

        /** @var SplFileInfo $file */
        foreach ($finder->in($this->options['cache_dir'])->files() as $file) {
            unlink($file->getRealpath());
        }
    }

    /**
     * Sets locales.
     *
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * Returns locales.
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Returns domains.
     *
     * @return array
     */
    public function getDomains()
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();

        /** @var TranslationDomainRepository $domainRepository */
        $domainRepository = $em->getRepository('ArgentumTranslationBundle:TranslationDomain');

        $domains = $domainRepository->getAllDomains();

        return $domains;
    }
}
