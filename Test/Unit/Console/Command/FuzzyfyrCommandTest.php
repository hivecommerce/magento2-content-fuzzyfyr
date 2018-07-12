<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use AllInData\ContentFuzzyfyr\Console\Command\FuzzyfyrCommand;

class FuzzyfyrCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FuzzyfyrCommand
     */
    private $command;

    public function setUp()
    {
        $this->command = new FuzzyfyrCommand();
    }

    public function testExecuteAnonymous()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                '-a' => true
            ]
        );

        $this->assertContains('Hello Anonymous!', $commandTester->getDisplay());
    }

    public function testExecuteName()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                FuzzyfyrCommand::NAME_ARGUMENT => 'Test'
            ]
        );

        $this->assertContains('Hello Test!', $commandTester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument name is missing
     */
    public function testExecuteError()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }
}
