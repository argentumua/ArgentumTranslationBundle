<?php

namespace Argentum\TranslationBundle\Tests\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;

/**
 * BaseCommandTest
 *
 * @author     Vadim Borodavko <javer@argentum.ua>
 * @copyright  Argentum IT Lab, http://argentum.ua
 */
abstract class BaseCommandTest extends WebTestCase
{
    protected $tempDir;

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if ($this->tempDir && is_dir($this->tempDir)) {
            foreach (Finder::create()->files()->in($this->tempDir) as $file) {
                unlink($file);
            }

            rmdir($this->tempDir);
        }

        parent::tearDown();
    }

    /**
     * Returns temporary directory.
     *
     * @return string
     */
    protected function getTempDir()
    {
        if (!$this->tempDir || !is_dir($this->tempDir)) {
            $this->tempDir = tempnam(sys_get_temp_dir(), 'transcommand-');

            if (!is_dir($this->tempDir) && is_file($this->tempDir)) {
                unlink($this->tempDir);
                mkdir($this->tempDir);
            }
        }

        return $this->tempDir;
    }

    /**
     * Returns command tester.
     *
     * @param Command $command
     *
     * @return CommandTester
     */
    protected function getCommandTester(Command $command)
    {
        $client = $this->makeClient();
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);
        $application->add($command);

        return new CommandTester($command);
    }
}
