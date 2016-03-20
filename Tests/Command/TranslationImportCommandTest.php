<?php

namespace Argentum\TranslationBundle\Tests\Command;

use Argentum\TranslationBundle\Command\TranslationImportCommand;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * TranslationImportCommandTest
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationImportCommandTest extends BaseCommandTest
{
    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected function setUp()
    {
        $this->loadFixtures(array());

        $this->commandTester = $this->getCommandTester(new TranslationImportCommand());
    }

    public function testImportDirectory()
    {
        file_put_contents($this->getTempDir() . '/test.ru.yml', "'test token' : 'test translation'\n");

        $statusCode = $this->commandTester->execute(array(
            'domain' => array('test'),
            '--locales' => 'ru',
            '--dir' => $this->getTempDir(),
        ));

        $this->assertEquals(0, $statusCode);
        $this->assertRegExp('/Importing.*test.ru/', $this->commandTester->getDisplay());
        $this->assertRegExp('/Import completed/', $this->commandTester->getDisplay());
        $this->assertRegExp('/Total (<[^>]+>)?1(<[^>]+>)? domains created/', $this->commandTester->getDisplay());
        $this->assertRegExp('/(<[^>]+>)?1(<[^>]+>)? translations created/', $this->commandTester->getDisplay());

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $domain = $em->getRepository('ArgentumTranslationBundle:TranslationDomain')->getByName('test');
        $this->assertNotNull($domain);
        $messages = $em->getRepository('ArgentumTranslationBundle:TranslationUnit')
            ->getMessagesForLocaleAndDomain('ru', 'test');
        $this->assertTrue(isset($messages['test token']) && ($messages['test token'] == 'test translation'));
    }

    public function testImportBundle()
    {
        $statusCode = $this->commandTester->execute(array(
            'domain' => array('translation'),
            '--bundles' => 'ArgentumTranslationBundle',
        ));

        $this->assertEquals(0, $statusCode);
        $this->assertRegExp('/Importing.*translation.ru/', $this->commandTester->getDisplay());
        $this->assertRegExp('/Import completed/', $this->commandTester->getDisplay());
        $this->assertRegExp('/Total (<[^>]+>)?1(<[^>]+>)? domains created/', $this->commandTester->getDisplay());
        $this->assertRegExp('/(<[^>]+>)?\d+(<[^>]+>)? translations created/', $this->commandTester->getDisplay());

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $domain = $em->getRepository('ArgentumTranslationBundle:TranslationDomain')->getByName('translation');
        $this->assertNotNull($domain);
        $messages = $em->getRepository('ArgentumTranslationBundle:TranslationUnit')
            ->getMessagesForLocaleAndDomain('ru', 'translation');
        $this->assertTrue(isset($messages['Translations']) && ($messages['Translations'] == 'Переводы'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following locales do not exist: zz
     */
    public function testWrongLocales()
    {
        $this->commandTester->execute(array(
            '--locales' => 'zz',
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The source directory "nonexistent" does not exist
     */
    public function testWrongDir()
    {
        $this->commandTester->execute(array(
            '--dir' => 'nonexistent',
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following bundles do not exist: nonexistent
     */
    public function testWrongBundle()
    {
        $this->commandTester->execute(array(
            '--bundles' => 'nonexistent',
        ));
    }
}
