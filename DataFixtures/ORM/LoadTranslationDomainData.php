<?php

namespace Argentum\TranslationBundle\DataFixtures\ORM;

use Argentum\TranslationBundle\Entity\TranslationDomain;
use Argentum\TranslationBundle\Entity\TranslationDomainTranslation;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadTranslationDomainData
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class LoadTranslationDomainData implements FixtureInterface
{
    protected $domains = array(
        array(
            'name' => 'domain',
            'translations' => array(
                'ru' => array(
                    'title' => 'domain title ru',
                ),
                'uk' => array(
                    'title' => 'domain title uk',
                ),
                'en' => array(
                    'title' => 'domain title en',
                ),
            )
        ),
    );

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domains as $data) {
            $domain = new TranslationDomain();
            $domain->setName($data['name']);

            foreach ($data['translations'] as $locale => $transData) {
                /** @var TranslationDomainTranslation $domainTrans */
                $domainTrans = $domain->translate($locale);
                $domainTrans->setTitle($transData['title']);
            }

            $manager->persist($domain);
            $domain->mergeNewTranslations();
            $manager->flush();
        }
    }
}
