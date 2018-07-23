<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Handler\Backup;

use AllInData\ContentFuzzyfyr\Handler\Backup\DatabaseHandler;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use AllInData\ContentFuzzyfyr\Console\Command\ExportCommand;
use AllInData\ContentFuzzyfyr\Handler\BackupHandler;
use Magento\Framework\Backup\Db\BackupInterface;
use Magento\Framework\Backup\Db\BackupDbInterface;
use Magento\Framework\Backup\Db\BackupFactory;
use Magento\Framework\EntityManager\EventManager;
use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Model\ConfigurationFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DatabaseHandlerTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Handler\Backup
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
        $configuration->expects($this->once())
            ->method('setApplyToUsers')
            ->with(true);
        $configuration->expects($this->once())
            ->method('setApplyToCustomers')
            ->with(true);
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects($this->once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects($this->once())
            ->method('dispatch')
            ->with(ExportCommand::EVENT_NAME, [
                'configuration' => $configuration
            ]);

        $backupHandler = $this->getBackupHandler();
        $backupHandler->expects($this->once())
            ->method('beginTransaction');
        $backupHandler->expects($this->once())
            ->method('endTransaction');

        $backupEntity = $this->createMock(BackupInterface::class);
        $backupEntity->expects($this->once())
            ->method('setTime')
            ->willReturn($backupEntity);
        $backupEntity->expects($this->once())
            ->method('setType')
            ->willReturn($backupEntity);
        $backupEntity->expects($this->once())
            ->method('setPath')
            ->willReturn($backupEntity);
        $backupEntity->expects($this->once())
            ->method('setName')
            ->willReturn($backupEntity);
        $backupDbEntity = $this->createMock(BackupDbInterface::class);
        $backupDbEntity->expects($this->once())
            ->method('createBackup')
            ->with($backupEntity);
        $backupFactory = $this->getBackupFactory();
        $backupFactory->expects($this->once())
            ->method('createBackupModel')
            ->willReturn($backupEntity);
        $backupFactory->expects($this->once())
            ->method('createBackupDbModel')
            ->willReturn($backupDbEntity);

        $databaseHandler = new DatabaseHandler(
            $backupFactory,
            $eventManager,
            $configurationFactory,
            $backupHandler
        );

        $this->assertEquals($databaseHandler, $databaseHandler->addIgnorePaths([]));
        $this->assertTrue($databaseHandler->create());
    }
    /**
     * @test
     */
    public function runFailsDueToBackupTaskIssues()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects($this->once())
            ->method('setApplyToUsers')
            ->with(true);
        $configuration->expects($this->once())
            ->method('setApplyToCustomers')
            ->with(true);
        $configurationFactory = $this->getConfigurationFactory();
        $configurationFactory->expects($this->once())
            ->method('create')
            ->willReturn($configuration);

        $eventManager = $this->getEventManager();
        $eventManager->expects($this->once())
            ->method('dispatch')
            ->with(ExportCommand::EVENT_NAME, [
                'configuration' => $configuration
            ]);

        $backupHandler = $this->getBackupHandler();
        $backupHandler->expects($this->once())
            ->method('beginTransaction');
        $backupHandler->expects($this->once())
            ->method('endTransaction');

        $backupEntity = $this->createMock(BackupInterface::class);
        $backupEntity->expects($this->once())
            ->method('setTime')
            ->willReturn($backupEntity);
        $backupEntity->expects($this->once())
            ->method('setType')
            ->willReturn($backupEntity);
        $backupEntity->expects($this->once())
            ->method('setPath')
            ->willReturn($backupEntity);
        $backupEntity->expects($this->once())
            ->method('setName')
            ->willReturn($backupEntity);
        $backupDbEntity = $this->createMock(BackupDbInterface::class);
        $backupDbEntity->expects($this->once())
            ->method('createBackup')
            ->with($backupEntity)
            ->willThrowException(new \Exception());
        $backupFactory = $this->getBackupFactory();
        $backupFactory->expects($this->once())
            ->method('createBackupModel')
            ->willReturn($backupEntity);
        $backupFactory->expects($this->once())
            ->method('createBackupDbModel')
            ->willReturn($backupDbEntity);

        $databaseHandler = new DatabaseHandler(
            $backupFactory,
            $eventManager,
            $configurationFactory,
            $backupHandler
        );

        $this->assertEquals(ExportCommand::ERROR_EXPORT_FAILED, $databaseHandler->create());
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