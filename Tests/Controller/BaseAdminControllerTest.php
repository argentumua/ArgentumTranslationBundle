<?php

namespace Argentum\TranslationBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * BaseAdminControllerTest
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
abstract class BaseAdminControllerTest extends WebTestCase
{
    protected $adminBaseUrl = '/admin';

    protected $baseRoutePattern;

    protected $buttonCreate = 'btn_create_and_edit';
    protected $buttonUpdate = 'btn_update_and_list';

    /** @var Client */
    protected $client;

    protected $tempFilename;

    protected $translationDomain = 'messages';

    /**
     * Removes temporary file and shuts the kernel down if it was used in the test.
     */
    protected function tearDown()
    {
        if ($this->tempFilename && is_file($this->tempFilename)) {
            unlink($this->tempFilename);
        }

        parent::tearDown();
    }

    /**
     * Returns the entity manager.
     *
     * @return EntityManager The entity manager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Returns the repository for an entity class.
     *
     * @param string $entityName The name of the entity.
     *
     * @return \Doctrine\ORM\EntityRepository The repository class.
     */
    protected function getRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
     * Returns the form name.
     *
     * @param Form $form The form
     *
     * @return string The form name
     */
    protected function getFormName(Form $form)
    {
        $node = $form->getFormNode();
        $actionUrl = $node->getAttribute('action');
        $formName = substr(strchr($actionUrl, '='), 1);

        return $formName;
    }

    /**
     * Sets the form values.
     *
     * @param Form $form The form
     * @param string $formName The form name
     * @param array $values The form values
     * @param array $translationValues The form translation values
     */
    protected function setFormValues(Form $form, $formName, $values, $translationValues = array())
    {
        if (is_array($translationValues) && count($translationValues) > 0) {
            $translations = array();

            foreach ($this->getLocales() as $locale) {
                $translations[$locale] = $translationValues;
            }

            $values['translations'] = $translations;
        }

        $form->setValues(array($formName => $values));
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @return string The translated string
     */
    protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (is_null($domain)) {
            $domain = $this->translationDomain;
        }

        return $this->getContainer()->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Returns an array of locales.
     *
     * @return array
     */
    protected function getLocales()
    {
        return $this->getContainer()->getParameter('locales');
    }

    /**
     * Returns requested page content.
     *
     * @return string
     */
    protected function getContent()
    {
        if ($this->client && ($response = $this->client->getResponse())) {
            return $response->getContent();
        }

        return '';
    }

    /**
     * Checks whether user has been redirected to page with success message.
     */
    protected function assertRedirectToSuccess()
    {
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertCount(1, $crawler->filter('div.alert-success'));
    }

    /**
     * Returns admin class base URL.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function getBaseUrl()
    {
        if (!$this->baseRoutePattern) {
            throw new \InvalidArgumentException('You should specify correct baseRoutePattern for your admin class.');
        }

        return sprintf('%s/%s', $this->adminBaseUrl, $this->baseRoutePattern);
    }

    /**
     * Tests list action.
     *
     * @return Crawler
     */
    protected function adminList()
    {
        $this->client = $this->makeClient();
        $crawler = $this->client->request('GET', sprintf('%s/list', $this->getBaseUrl()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        return $crawler;
    }

    /**
     * Tests create action.
     *
     * @return object New object
     */
    protected function adminCreate()
    {
        $this->client = $this->makeClient();
        $crawler = $this->client->request('GET', sprintf('%s/create', $this->getBaseUrl()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->selectButton($this->buttonCreate)->form();
        $this->getFormData($form, $this->getFormName($form));

        $this->client->submit($form);

        $this->assertRedirectToSuccess();

        $object = $this->getNewObject();
        $this->assertNotNull($object);

        $this->validateNewObject($object);

        return $object;
    }

    /**
     * Tests edit action.
     *
     * @return object New object
     */
    protected function adminEdit()
    {
        $object = $this->getObject();
        $this->assertNotNull($object);

        $this->getEntityManager()->detach($object);

        $this->client = $this->makeClient();
        $crawler = $this->client->request('GET', sprintf('%s/%d/edit', $this->getBaseUrl(), $object->getId()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->selectButton($this->buttonUpdate)->form();
        $this->getFormData($form, $this->getFormName($form));

        $this->client->submit($form);

        $this->assertRedirectToSuccess();

        $object = $this->getNewObject();
        $this->assertNotNull($object);

        $this->validateNewObject($object);

        return $object;
    }

    /**
     * Tests delete action.
     */
    protected function adminDelete()
    {
        $object = $this->getObject();
        $this->assertNotNull($object);

        $this->getEntityManager()->detach($object);

        $this->client = $this->makeClient();
        $crawler = $this->client->request('GET', sprintf('%s/%d/delete', $this->getBaseUrl(), $object->getId()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filter('input[name="_method"][value="DELETE"]')->parents()->form();
        $this->client->submit($form);

        $this->assertRedirectToSuccess();

        $object = $this->getObject();
        $this->assertNull($object);
    }

    /**
     * Tests show action.
     *
     * @return object
     */
    protected function adminShow()
    {
        $object = $this->getObject();
        $this->assertNotNull($object);

        $this->client = $this->makeClient();
        $this->client->request('GET', sprintf('%s/%d/show', $this->getBaseUrl(), $object->getId()));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        return $object;
    }

    /**
     * Returns a test object by key.
     *
     * @param string $key A unique key
     *
     * @return object An object
     */
    abstract protected function getObject($key = null);

    /**
     * Returns a new test object by key.
     *
     * @param string $key A unique key
     *
     * @return object An object
     */
    abstract protected function getNewObject($key = null);

    /**
     * Returns the form data.
     *
     * @param Form $form The form
     * @param string $formName The form name
     */
    abstract protected function getFormData(Form $form, $formName);

    /**
     * Validates the new object.
     *
     * @param object $object The object
     */
    abstract protected function validateNewObject($object);
}
