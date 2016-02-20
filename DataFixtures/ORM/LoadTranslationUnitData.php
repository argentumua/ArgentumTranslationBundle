<?php

namespace Argentum\TranslationBundle\DataFixtures\ORM;

use Argentum\TranslationBundle\Entity\TranslationUnit;
use Argentum\TranslationBundle\Entity\TranslationUnitTranslation;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadTranslationUnitData
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class LoadTranslationUnitData implements FixtureInterface
{
    protected $units = array(
        array(
            'domain' => 'domain',
            'token' => 'token',
            'translations' => array(
                'ru' => array(
                    'content' => 'token translation ru',
                ),
                'uk' => array(
                    'content' => 'token translation uk',
                ),
                'en' => array(
                    'content' => 'token translation en',
                ),
            )
        ),
    );

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->units as $data) {
            $unit = new TranslationUnit();
            $unit->setToken($data['token']);

            $domain = $manager->getRepository('ArgentumTranslationBundle:TranslationDomain')
                ->getByName($data['domain']);
            $unit->setDomain($domain);

            foreach ($data['translations'] as $locale => $transData) {
                /** @var TranslationUnitTranslation $unitTrans */
                $unitTrans = $unit->translate($locale, false);
                $unitTrans->setContent($transData['content']);
            }

            $manager->persist($unit);
            $unit->mergeNewTranslations();
            $manager->flush();
        }
    }
}
