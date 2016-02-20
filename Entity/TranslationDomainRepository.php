<?php

namespace Argentum\TranslationBundle\Entity;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;

/**
 * TranslationDomainRepository
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationDomainRepository extends EntityRepository
{
    /**
     * Returns a list of all domains.
     *
     * @return array
     */
    public function getAllDomains()
    {
        try {
            $result = $this->createQueryBuilder('d')
                ->select('d.name')
                ->getQuery()
                ->getArrayResult();
            $domains = array_map('current', $result);
        } catch (DBALException $e) {
            $domains = array();
        }

        return $domains;
    }

    /**
     * Returns a domain by name.
     *
     * @param string $name
     *
     * @return TranslationDomain
     */
    public function getByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }
}
