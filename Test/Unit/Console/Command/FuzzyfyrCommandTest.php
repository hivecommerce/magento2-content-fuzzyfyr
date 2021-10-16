<?php

/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Console\Command;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EventManager;
use HiveCommerce\ContentFuzzyfyr\Model\ConfigurationFactory;
use HiveCommerce\ContentFuzzyfyr\Console\Command\FuzzyfyrCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FuzzyfyrCommandTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Console\Command
 */
class FuzzyfyrCommandTest extends AbstractTest
{
    /**
     * @test
     */
    public function runSuccessfully()
    {
        $state = $this->getState();
        $state->expects(self::any())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_DEFAULT);

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects(self::once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects(self::once())
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

        self::assertEquals(FuzzyfyrCommand::SUCCESS, $command->run($input, $output));
    }

    /**
     * @test
     */
    public function runSuccessfullyInProductionModeWithForceOption()
    {
        $state = $this->getState();
        $state->expects(self::any())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects(self::once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects(self::once())
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
        $input->expects(self::any())
            ->method('getOption')
            ->willReturnCallback(function ($name) {
                switch($name) {
                    default: return null;
                    case FuzzyfyrCommand::FLAG_FORCE: return true;
                    case FuzzyfyrCommand::FLAG_ONLY_EMPTY: return true;
                    case FuzzyfyrCommand::FLAG_CATEGORIES: return false;
                    case FuzzyfyrCommand::FLAG_CMS_BLOCKS: return false;
                    case FuzzyfyrCommand::FLAG_CMS_PAGES: return false;
                    case FuzzyfyrCommand::FLAG_CUSTOMERS: return false;
                    case FuzzyfyrCommand::FLAG_PRODUCTS: return false;
                    case FuzzyfyrCommand::FLAG_USERS: return false;
                    case FuzzyfyrCommand::OPTION_DUMMY_CONTENT_TEXT: return FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_TEXT;
                    case FuzzyfyrCommand::OPTION_DUMMY_CONTENT_PASSWORD: return FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_PASSWORD;
                    case FuzzyfyrCommand::OPTION_DUMMY_CONTENT_EMAIL: return FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_EMAIL;
                    case FuzzyfyrCommand::OPTION_DUMMY_CONTENT_URL: return FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_URL;
                    case FuzzyfyrCommand::OPTION_DUMMY_CONTENT_PHONE: return FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_PHONE;
                    case FuzzyfyrCommand::OPTION_DUMMY_CONTENT_IMAGE_PATH: return FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_IMAGE_PATH;
                }

            });
        $output = $this->getOutput();

        self::assertEquals(FuzzyfyrCommand::SUCCESS, $command->run($input, $output));
    }

    /**
     * @test
     */
    public function runFailsInProductionMode()
    {
        $state = $this->getState();
        $state->expects(self::any())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);

        $eventManager = $this->getEventManager();
        $eventManager->expects(self::never())
            ->method('dispatch');

        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects(self::never())
            ->method('create');

        $command = new FuzzyfyrCommand(
            $state,
            $eventManager,
            $configurationFactory
        );

        $input = $this->getInput();
        $output = $this->getOutput();

        self::assertEquals(FuzzyfyrCommand::ERROR_PRODUCTION_MODE, $command->run($input, $output));
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
