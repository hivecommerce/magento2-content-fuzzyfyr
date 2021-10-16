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

use HiveCommerce\ContentFuzzyfyr\Handler\BackupHandler;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EventManager;
use HiveCommerce\ContentFuzzyfyr\Model\ConfigurationFactory;
use HiveCommerce\ContentFuzzyfyr\Console\Command\ExportCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportCommandTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Console\Command
 */
class ExportCommandTest extends AbstractTest
{
    /**
     * @test
     */
    public function runSuccessfully()
    {
        $state = $this->getState();

        $input = $this->getInput();
        $input->expects(self::any())
            ->method('getOption')
            ->willReturnCallback(function ($name): string {
                if ($name === ExportCommand::OPTION_DUMP_OUTPUT) {
                    return ExportCommand::DEFAULT_DUMP_OUTPUT;
                }
                return '';
            });
        $output = $this->getOutput();

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects(self::once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects(self::once())
            ->method('dispatch')
            ->with(ExportCommand::EVENT_NAME, [
                'configuration' => $configuration
            ]);

        $backupHandler = $this->getBackupHandler();
        $backupHandler->expects(self::once())
            ->method('beginTransaction');
        $backupHandler->expects(self::once())
            ->method('endTransaction');
        $backupHandler->expects(self::once())
            ->method('run')
            ->with($output, ExportCommand::DEFAULT_DUMP_OUTPUT);

        $command = new ExportCommand(
            $state,
            $eventManager,
            $configurationFactory,
            $backupHandler
        );

        self::assertEquals(ExportCommand::SUCCESS, $command->run($input, $output));
    }

    /**
     * @test
     */
    public function runFailesRunningBackup()
    {
        $state = $this->getState();

        $input = $this->getInput();
        $input->expects(self::any())
            ->method('getOption')
            ->willReturnCallback(function ($name): string {
                if ($name === ExportCommand::OPTION_DUMP_OUTPUT) {
                    return ExportCommand::DEFAULT_DUMP_OUTPUT;
                }
                return '';
            });
        $output = $this->getOutput();

        $configuration = $this->getMockBuilder(Configuration::class)->getMock();
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects(self::once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects(self::once())
            ->method('dispatch')
            ->with(ExportCommand::EVENT_NAME, [
                'configuration' => $configuration
            ]);

        $backupHandler = $this->getBackupHandler();
        $backupHandler->expects(self::once())
            ->method('beginTransaction');
        $backupHandler->expects(self::once())
            ->method('endTransaction');
        $backupHandler->expects(self::once())
            ->method('run')
            ->with($output, ExportCommand::DEFAULT_DUMP_OUTPUT)
            ->willThrowException(new \Exception());

        $command = new ExportCommand(
            $state,
            $eventManager,
            $configurationFactory,
            $backupHandler
        );

        self::assertEquals(ExportCommand::ERROR_EXPORT_FAILED, $command->run($input, $output));
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
     * @return MockObject|BackupHandler
     */
    private function getBackupHandler()
    {
        return $this->getMockBuilder(BackupHandler::class)
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
