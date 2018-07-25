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
        $state->expects($this->any())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_DEFAULT);

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects($this->once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects($this->once())
            ->method('dispatch')
            ->with(FuzzyfyrCommand::EVENT_NAME, [
                'configuration' => $configuration
            ]);

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
    public function runSuccessfullyInProductionModeWithForceOption()
    {
        $state = $this->getState();
        $state->expects($this->any())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects($this->once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects($this->once())
            ->method('dispatch')
            ->with(FuzzyfyrCommand::EVENT_NAME, [
                'configuration' => $configuration
            ]);

        $command = new FuzzyfyrCommand(
            $state,
            $eventManager,
            $configurationFactory
        );

        $input = $this->getInput();
        $i = 4;
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_FORCE)
            ->willReturn(true);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_ONLY_EMPTY)
            ->willReturn(true);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_CATEGORIES)
            ->willReturn(false);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_CMS_BLOCKS)
            ->willReturn(false);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_CMS_PAGES)
            ->willReturn(false);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_CUSTOMERS)
            ->willReturn(false);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_PRODUCTS)
            ->willReturn(false);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::FLAG_USERS)
            ->willReturn(false);

        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::OPTION_DUMMY_CONTENT_TEXT)
            ->willReturn(FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_TEXT);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::OPTION_DUMMY_CONTENT_PASSWORD)
            ->willReturn(FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_PASSWORD);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::OPTION_DUMMY_CONTENT_EMAIL)
            ->willReturn(FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_EMAIL);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::OPTION_DUMMY_CONTENT_URL)
            ->willReturn(FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_URL);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::OPTION_DUMMY_CONTENT_PHONE)
            ->willReturn(FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_PHONE);
        $input->expects($this->at($i++))
            ->method('getOption')
            ->with(FuzzyfyrCommand::OPTION_DUMMY_CONTENT_IMAGE_PATH)
            ->willReturn(FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_IMAGE_PATH);

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
        $eventManager->expects($this->never())
            ->method('dispatch');

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