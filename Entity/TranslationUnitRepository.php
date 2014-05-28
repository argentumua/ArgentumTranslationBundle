<?php

namespace Argentum\TranslationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * TranslationUnitRepository
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationUnitRepository extends EntityRepository
{
    /**
     * Returns all messages for the given locale and domain.
     *
     * @param string $locale
     * @param string $domain
     *
     * @return array
     */
    public function getMessagesForLocaleAndDomain($locale, $domain)
    {
        $messages = array();

        /** @var TranslationDomainRepository $domainRepository */
        $domainRepository = $this->getEntityManager()
            ->getRepository('ArgentumTranslationBundle:TranslationDomain');
        $transDomain = $domainRepository->getByName($domain);

        if ($transDomain) {
            $query = $this->getEntityManager()->createQueryBuilder()
                ->select('u.token')
                ->from('ArgentumTranslationBundle:TranslationUnit', 'u')
                ->where('u.domain = :domain')
                ->setParameter('domain', $transDomain->getId())
                ->addSelect('t.content')
                ->leftJoin('u.translations', 't', Join::WITH, 't.locale = :locale')
                ->setParameter('locale', $locale)
                ->getQuery();

            $transUnits = $query->getArrayResult();

            foreach ($transUnits as $unit) {
                $messages[$unit['token']] = $unit['content'];
            }
        }

        return $messages;
    }

    /**
     * Finds the message or creates a new one.
     *
     * @param string $token
     * @param TranslationDomain $transDomain
     *
     * @return TranslationUnit
     */
    public function findOrCreateByTokenAndDomain($token, $transDomain)
    {
        $unit = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('ArgentumTranslationBundle:TranslationUnit', 'u')
            ->where('u.domain = :domain AND u.token = :token')
            ->setParameter('domain', $transDomain->getId())
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$unit) {
            $unit = new TranslationUnit();
            $unit->setDomain($transDomain);
            $unit->setToken($token);
        }

        return $unit;
    }
}
