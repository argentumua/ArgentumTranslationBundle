<?php

namespace Argentum\TranslationBundle\EventListener;

use Argentum\TranslationBundle\Entity\TranslationDomain;
use Argentum\TranslationBundle\Entity\TranslationDomainTranslation;
use Argentum\TranslationBundle\Entity\TranslationUnit;
use Argentum\TranslationBundle\Entity\TranslationUnitTranslation;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TranslationListener
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Clears the translation cache.
     *
     * @param object $entity
     */
    private function clearTranslationCache($entity)
    {
        if (($entity instanceof TranslationUnit)
            || ($entity instanceof TranslationUnitTranslation)
            || ($entity instanceof TranslationDomain)
            || ($entity instanceof TranslationDomainTranslation)) {
            $this->container
                ->get('argentum.translation.translator')
                ->clearCache();
        }
    }

    /**
     * Post-persist event handler.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->clearTranslationCache($args->getEntity());
    }

    /**
     * Post-update event handler.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->clearTranslationCache($args->getEntity());
    }

    /**
     * Post-remove event handler.
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->clearTranslationCache($args->getEntity());
    }
}
