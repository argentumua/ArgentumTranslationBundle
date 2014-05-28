<?php

namespace Argentum\TranslationBundle\Tests\Controller;

use Argentum\TranslationBundle\Entity\TranslationDomain;
use Argentum\TranslationBundle\Entity\TranslationDomainRepository;
use Argentum\TranslationBundle\Entity\TranslationUnit;
use Argentum\TranslationBundle\Entity\TranslationUnitRepository;
use Symfony\Component\DomCrawler\Form;

/**
 * TranslationUnitAdminControllerTest
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationUnitAdminControllerTest extends BaseAdminControllerTest
{
    protected $baseRoutePattern = 'argentum/translation/translationunit';

    protected $translationDomain = 'translation';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->loadFixtures(array(
            'Argentum\TranslationBundle\DataFixtures\ORM\LoadTranslationDomainData',
            'Argentum\TranslationBundle\DataFixtures\ORM\LoadTranslationUnitData',
        ));
    }

    /**
     * Returns domain by name.
     *
     * @param string $name
     *
     * @return TranslationDomain
     */
    protected function getDomain($name = 'domain')
    {
        /** @var TranslationDomainRepository $repository */
        $repository = $this->getRepository('ArgentumTranslationBundle:TranslationDomain');

        return $repository->getByName($name);
    }

    /**
     * Returns unit by token.
     *
     * @param string $key
     *
     * @return TranslationUnit
     */
    protected function getObject($key = 'token')
    {
        /** @var TranslationUnitRepository $repository */
        $repository = $this->getRepository('ArgentumTranslationBundle:TranslationUnit');

        return $repository->findOneBy(array(
            'domain' => $this->getDomain()->getId(),
            'token' => $key,
        ));
    }

    /**
     * Returns a new test object by key.
     *
     * @param string $key A unique key
     *
     * @return TranslationUnit
     */
    protected function getNewObject($key = null)
    {
        return $this->getObject('test');
    }

    /**
     * Returns the form data.
     *
     * @param Form $form The form
     * @param string $formName The form name
     */
    protected function getFormData(Form $form, $formName)
    {
        $domain = array(
            'domain' => $this->getDomain()->getId(),
            'token' => 'test',
        );

        $translations = array(
            'content' => 'content',
        );

        $this->setFormValues($form, $formName, $domain, $translations);
    }

    /**
     * Validates the new object.
     *
     * @param TranslationUnit $object The object
     */
    protected function validateNewObject($object)
    {
        $this->assertEquals($this->getDomain(), $object->getDomain());
        $this->assertEquals('test', $object->getToken());
        $this->assertEquals('content', $object->getContent());
    }

    public function testUnitList()
    {
        $crawler = $this->adminList();

        $this->assertContains($this->trans('Translation Unit List'), $this->getContent());
        $this->assertCount(1, $crawler->filter('html:contains("token")'));
    }

    public function testUnitCreate()
    {
        $this->adminCreate();
    }

    public function testUnitEdit()
    {
        $this->adminEdit();
    }

    public function testUnitDelete()
    {
        $this->adminDelete();
    }
}
