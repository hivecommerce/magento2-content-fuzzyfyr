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

use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EventManager;
use AllInData\ContentFuzzyfyr\Model\ConfigurationFactory;
use AllInData\ContentFuzzyfyr\Console\Command\FuzzyfyrCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FuzzyfyrCommandTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Console\Command
 */
class FuzzyfyrCommandTest extends AbstractTest
{
    /**
     * @test
     */
    public function runSuccessfully()
    {
        $state = $this->getState();

        $eventManager = $this->getEventManager();

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects($this->once())
            ->method('create')
            ->willReturn($configuration);

        $command = new FuzzyfyrCommand(
            $state,
            $eventManager,
            $configurationFactory
        );

        $input = $this->getInput();
        $output = $this->getOutput();

        $this->assertEquals(FuzzyfyrCommand::SUCCESS, $command->run($input, $output));
    }
    /**
     * @test
     */
    public function runFailsInProductionMode()
    {
        $state = $this->getState();
        $state->expects($this->any())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);

        $eventManager = $this->getEventManager();

        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects($this->never())
            ->method('create');

        $command = new FuzzyfyrCommand(
            $state,
            $eventManager,
            $configurationFactory
        );

        $input = $this->getInput();
        $output = $this->getOutput();

        $this->assertEquals(FuzzyfyrCommand::ERROR_PRODUCTION_MODE, $command->run($input, $output));
    }

    /**
     * @return MockObject|State
     */
    private function getState()
    {
        return $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|EventManager
     */
    private function getEventManager()
    {
        return $this->getMockBuilder(EventManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|ConfigurationFactory
     */
    private function getConfigurationFactory()
    {
        return $this->getMockBuilder(ConfigurationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|InputInterface
     */
    private function getInput()
    {
        return $this->getMockBuilder(InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|OutputInterface
     */
    private function getOutput()
    {
        return $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}