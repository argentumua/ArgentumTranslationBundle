<?php

namespace Argentum\TranslationBundle\Tests\Command;

use Argentum\TranslationBundle\Command\TranslationExportCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;

/**
 * TranslationExportCommandTest
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
class TranslationExportCommandTest extends BaseCommandTest
{
    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected function setUp()
    {
        $this->loadFixtures(array(
            'Argentum\TranslationBundle\DataFixtures\ORM\LoadTranslationDomainData',
            'Argentum\TranslationBundle\DataFixtures\ORM\LoadTranslationUnitData',
        ));

        $this->commandTester = $this->getCommandTester(new TranslationExportCommand());
    }

    public function testExportSuccess()
    {
        $statusCode = $this->commandTester->execute(array(
            'domain' => array('domain'),
            '--locales' => 'ru,en',
            '--format' => 'yml',
            '--dir' => $this->getTempDir(),
        ));

        $this->assertEquals(0, $statusCode);
        $this->assertRegExp('/Exporting domain:/', $this->commandTester->getDisplay());
        $this->assertRegExp('/All done/', $this->commandTester->getDisplay());

        $this->assertEquals(2, Finder::create()->files()->in($this->getTempDir())->count());

        $filename = $this->getTempDir() . '/domain.ru.yml';
        $this->assertTrue(file_exists($filename));
        $this->assertRegExp("/token: 'token translation ru'/", file_get_contents($filename));
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
     * @expectedExceptionMessage The following domains do not exist: nonexistent
     */
    public function testWrongDomains()
    {
        $this->commandTester->execute(array(
            'domain' => array('nonexistent'),
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The target directory "nonexistent" does not exist
     */
    public function testWrongDir()
    {
        $this->commandTester->execute(array(
            '--dir' => 'nonexistent',
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid output format "nonexistent"
     */
    public function testWrongFormat()
    {
        $this->commandTester->execute(array(
            '--format' => 'nonexistent',
        ));
    }
}
