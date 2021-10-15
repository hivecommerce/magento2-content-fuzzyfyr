<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Handler;

use HiveCommerce\ContentFuzzyfyr\Handler\BackupHandler;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
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
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Handler
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
        $maintenanceMode->expects($this->once())
            ->method('isOn')
            ->willReturn(false);

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
     */
    public function runFailsOnCopyingDumpFile()
    {
        $this->expectException(\RuntimeException::class);

        $expectedBackupPath = sys_get_temp_dir();

        $output = $this->getOutput();

        $maintenanceMode = $this->getMaintenanceMode();
        $maintenanceMode->expects($this->once())
            ->method('isOn')
            ->willReturn(false);
        $maintenanceMode->expects($this->once())
            ->method('set')
            ->with(true);

        $backupRollback = $this->getMockBuilder(BackupRollback::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backupRollback->expects($this->once())
            ->method('dbBackup')
            ->willReturn('/mybackup.bak');
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
     */
    public function runFailsOnCreatingBackupFolder()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not create backup folder: "/tmp"');

        $expectedBackupPath = sys_get_temp_dir();

        $output = $this->getOutput();

        $maintenanceMode = $this->getMaintenanceMode();
        $maintenanceMode->expects($this->once())
            ->method('isOn')
            ->willReturn(false);
        $maintenanceMode->expects($this->once())
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
