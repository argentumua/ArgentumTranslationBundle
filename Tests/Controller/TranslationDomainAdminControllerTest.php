<?php

namespace Argentum\TranslationBundle\Tests\Controller;

use Argentum\TranslationBundle\Entity\TranslationDomain;
use Argentum\TranslationBundle\Entity\TranslationDomainRepository;
use Symfony\Component\DomCrawler\Form;

/**
 * TranslationDomainAdminControllerTest
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationDomainAdminControllerTest extends BaseAdminControllerTest
{
    protected $baseRoutePattern = 'argentum/translation/translationdomain';

    protected $translationDomain = 'translation';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->loadFixtures(array(
            'Argentum\TranslationBundle\DataFixtures\ORM\LoadTranslationDomainData',
        ));
    }

    /**
     * Returns domain by name.
     *
     * @param string $key
     *
     * @return TranslationDomain
     */
    protected function getObject($key = 'domain')
    {
        /** @var TranslationDomainRepository $repository */
        $repository = $this->getRepository('ArgentumTranslationBundle:TranslationDomain');

        return $repository->getByName($key);
    }

    /**
     * Returns a new test object by key.
     *
     * @param string $key A unique key
     *
     * @return TranslationDomain
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
            'name' => 'test',
        );

        $translations = array(
            'title' => 'title',
        );

        $this->setFormValues($form, $formName, $domain, $translations);
    }

    /**
     * Validates the new object.
     *
     * @param TranslationDomain $object The object
     */
    protected function validateNewObject($object)
    {
        $this->assertEquals('test', $object->getName());
        $this->assertEquals('title', $object->getTitle());
    }

    public function testDomainList()
    {
        $crawler = $this->adminList();

        $this->assertContains($this->trans('Translation Domain List'), $this->getContent());
        $this->assertCount(1, $crawler->filter('html:contains("domain")'));
    }

    public function testDomainCreate()
    {
        $this->adminCreate();
    }

    public function testDomainEdit()
    {
        $this->adminEdit();
    }

    public function testDomainDelete()
    {
        $this->adminDelete();
    }
}
