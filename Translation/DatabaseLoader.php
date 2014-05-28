<?php

namespace Argentum\TranslationBundle\Translation;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Doctrine\ORM\EntityManager;

/**
 * DatabaseLoader loads translations from a database.
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class DatabaseLoader implements LoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);

        $messages = $this->entityManager
            ->getRepository('ArgentumTranslationBundle:TranslationUnit')
            ->getMessagesForLocaleAndDomain($locale, $domain);

        $catalogue->add($messages, $domain);

        return $catalogue;
    }
}
