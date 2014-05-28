<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Liip\FunctionalTestBundle\LiipFunctionalTestBundle(),

            new Argentum\TranslationBundle\ArgentumTranslationBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/ArgentumTranslationBundle/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/ArgentumTranslationBundle/logs';
    }
}
