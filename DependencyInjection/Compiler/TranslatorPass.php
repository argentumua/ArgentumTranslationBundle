<?php

namespace Argentum\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * TranslatorPass
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslatorPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('argentum.translation.translator')) {
            $translator = $container->findDefinition('argentum.translation.translator');
            $translatorDefault = $container->findDefinition('translator.default');

            $translator
                ->setArguments($translatorDefault->getArguments())
                ->setMethodCalls($translatorDefault->getMethodCalls())
                ->addMethodCall('setLocales', array($container->getParameter('locales')))
                ->addMethodCall('addDatabaseResources', array());

            $container->setAlias('translator', 'argentum.translation.translator');
        }
    }
}
