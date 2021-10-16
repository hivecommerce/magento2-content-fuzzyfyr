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

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Handler\Backup;

use HiveCommerce\ContentFuzzyfyr\Handler\Backup\DatabaseHandler;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use HiveCommerce\ContentFuzzyfyr\Console\Command\ExportCommand;
use HiveCommerce\ContentFuzzyfyr\Handler\BackupHandler;
use Magento\Framework\Backup\Db\BackupInterface;
use Magento\Framework\Backup\Db\BackupDbInterface;
use Magento\Framework\Backup\Db\BackupFactory;
use Magento\Framework\EntityManager\EventManager;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Model\ConfigurationFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DatabaseHandlerTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Handler\Backup
 */
class DatabaseHandlerTest extends AbstractTest
{
    /**
     * @test
     */
    public function runSuccessfully()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects(self::once())
            ->method('setApplyToUsers')
            ->with(true);
        $configuration->expects(self::once())
            ->method('setApplyToCustomers')
            ->with(true);
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

        $backupEntity = $this->createMock(BackupInterface::class);
        $backupEntity->expects(self::once())
            ->method('setTime')
            ->willReturn($backupEntity);
        $backupEntity->expects(self::once())
            ->method('setType')
            ->willReturn($backupEntity);
        $backupEntity->expects(self::once())
            ->method('setPath')
            ->willReturn($backupEntity);
        $backupEntity->expects(self::once())
            ->method('setName')
            ->willReturn($backupEntity);
        $backupDbEntity = $this->createMock(BackupDbInterface::class);
        $backupDbEntity->expects(self::once())
            ->method('createBackup')
            ->with($backupEntity);
        $backupFactory = $this->getBackupFactory();
        $backupFactory->expects(self::once())
            ->method('createBackupModel')
            ->willReturn($backupEntity);
        $backupFactory->expects(self::once())
            ->method('createBackupDbModel')
            ->willReturn($backupDbEntity);

        $databaseHandler = new DatabaseHandler(
            $backupFactory,
            $eventManager,
            $configurationFactory,
            $backupHandler
        );

        self::assertEquals($databaseHandler, $databaseHandler->addIgnorePaths([]));
        self::assertTrue($databaseHandler->create());
    }
    /**
     * @test
     */
    public function runFailsDueToBackupTaskIssues()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects(self::once())
            ->method('setApplyToUsers')
            ->with(true);
        $configuration->expects(self::once())
            ->method('setApplyToCustomers')
            ->with(true);
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

        $backupEntity = $this->createMock(BackupInterface::class);
        $backupEntity->expects(self::once())
            ->method('setTime')
            ->willReturn($backupEntity);
        $backupEntity->expects(self::once())
            ->method('setType')
            ->willReturn($backupEntity);
        $backupEntity->expects(self::once())
            ->method('setPath')
            ->willReturn($backupEntity);
        $backupEntity->expects(self::once())
            ->method('setName')
            ->willReturn($backupEntity);
        $backupDbEntity = $this->createMock(BackupDbInterface::class);
        $backupDbEntity->expects(self::once())
            ->method('createBackup')
            ->with($backupEntity)
            ->willThrowException(new \Exception());
        $backupFactory = $this->getBackupFactory();
        $backupFactory->expects(self::once())
            ->method('createBackupModel')
            ->willReturn($backupEntity);
        $backupFactory->expects(self::once())
            ->method('createBackupDbModel')
            ->willReturn($backupDbEntity);

        $databaseHandler = new DatabaseHandler(
            $backupFactory,
            $eventManager,
            $configurationFactory,
            $backupHandler
        );

        self::assertEquals(ExportCommand::ERROR_EXPORT_FAILED, $databaseHandler->create());
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
     * @return MockObject|BackupFactory
     */
    private function getBackupFactory()
    {
        return $this->getMockBuilder(BackupFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
