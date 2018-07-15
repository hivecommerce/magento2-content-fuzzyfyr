<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Handler;

use AllInData\ContentFuzzyfyr\Handler\BackupHandler;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class BackupHandlerTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Handler
 */
class BackupHandlerTest extends AbstractTest
{
    /**
     * @test
     */
    public function runSuccessfully()
    {
        $expectedBackupPath = sys_get_temp_dir();

        $output = $this->getOutput();

        $maintenanceMode = $this->getMaintenanceMode();
        $maintenanceMode->expects($this->at(0))
            ->method('isOn')
            ->willReturn(false);
        $maintenanceMode->expects($this->at(1))
            ->method('set')
            ->with(true);
        $maintenanceMode->expects($this->at(2))
            ->method('set')
            ->with(false);

        $backupRollback = $this->getMockBuilder(BackupRollback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backupRollback->expects($this->once())
            ->method('dbBackup');
        $backupRollbackFactory = $this->getBackupRollbackFactory();
        $backupRollbackFactory->expects($this->once())
            ->method('create')
            ->with($output)
            ->willReturn($backupRollback);

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('rollBack');
        $setup = $this->getModuleDataSetup();
        $setup->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connection);

        $ioFile = $this->getFile();
        $ioFile->expects($this->once())
            ->method('checkAndCreateFolder')
            ->with($expectedBackupPath, BackupHandler::BACKUP_DIRECTORY_FILEMODE)
            ->willReturn(true);
        $ioFile->expects($this->once())
            ->method('cp')
            ->willReturn(true);

        $backupHandler = new BackupHandler(
            $maintenanceMode,
            $backupRollbackFactory,
            $setup,
            $ioFile
        );

        $backupHandler->beginTransaction();
        $backupHandler->run($output, $expectedBackupPath);
        $backupHandler->endTransaction();
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function runFailsOnCopyingDumpFile()
    {
        $expectedBackupPath = sys_get_temp_dir();

        $output = $this->getOutput();

        $maintenanceMode = $this->getMaintenanceMode();
        $maintenanceMode->expects($this->at(0))
            ->method('isOn')
            ->willReturn(false);
        $maintenanceMode->expects($this->at(1))
            ->method('set')
            ->with(true);

        $backupRollback = $this->getMockBuilder(BackupRollback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backupRollback->expects($this->once())
            ->method('dbBackup');
        $backupRollbackFactory = $this->getBackupRollbackFactory();
        $backupRollbackFactory->expects($this->once())
            ->method('create')
            ->with($output)
            ->willReturn($backupRollback);

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('beginTransaction');
        $setup = $this->getModuleDataSetup();
        $setup->expects($this->exactly(1))
            ->method('getConnection')
            ->willReturn($connection);

        $ioFile = $this->getFile();
        $ioFile->expects($this->once())
            ->method('checkAndCreateFolder')
            ->with($expectedBackupPath, BackupHandler::BACKUP_DIRECTORY_FILEMODE)
            ->willReturn(true);
        $ioFile->expects($this->once())
            ->method('cp')
            ->willReturn(false);

        $backupHandler = new BackupHandler(
            $maintenanceMode,
            $backupRollbackFactory,
            $setup,
            $ioFile
        );

        $backupHandler->beginTransaction();
        $backupHandler->run($output, $expectedBackupPath);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not create backup folder: "/tmp"
     */
    public function runFailsOnCreatingBackupFolder()
    {
        $expectedBackupPath = sys_get_temp_dir();

        $output = $this->getOutput();

        $maintenanceMode = $this->getMaintenanceMode();
        $maintenanceMode->expects($this->at(0))
            ->method('isOn')
            ->willReturn(false);
        $maintenanceMode->expects($this->at(1))
            ->method('set')
            ->with(true);

        $backupRollbackFactory = $this->getBackupRollbackFactory();

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('beginTransaction');
        $setup = $this->getModuleDataSetup();
        $setup->expects($this->exactly(1))
            ->method('getConnection')
            ->willReturn($connection);

        $ioFile = $this->getFile();
        $ioFile->expects($this->once())
            ->method('checkAndCreateFolder')
            ->with($expectedBackupPath, BackupHandler::BACKUP_DIRECTORY_FILEMODE)
            ->willReturn(false);

        $backupHandler = new BackupHandler(
            $maintenanceMode,
            $backupRollbackFactory,
            $setup,
            $ioFile
        );

        $backupHandler->beginTransaction();
        $backupHandler->run($output, $expectedBackupPath);
    }

    /**
     * @return MockObject|MaintenanceMode
     */
    private function getMaintenanceMode()
    {
        return $this->getMockBuilder(MaintenanceMode::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|BackupRollbackFactory
     */
    private function getBackupRollbackFactory()
    {
        return $this->getMockBuilder(BackupRollbackFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|ModuleDataSetupInterface
     */
    private function getModuleDataSetup()
    {
        return $this->getMockBuilder(ModuleDataSetupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|File
     */
    private function getFile()
    {
        return $this->getMockBuilder(File::class)
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