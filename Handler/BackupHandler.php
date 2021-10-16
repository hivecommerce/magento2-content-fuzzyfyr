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

namespace HiveCommerce\ContentFuzzyfyr\Handler;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Filesystem\Io\File;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BackupHandler
 * @package HiveCommerce\ContentFuzzyfyr\Handler
 */
class BackupHandler
{
    /*
     * Defaults
     */
    const BACKUP_DIRECTORY_FILEMODE = 0750;

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;
    /**
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;
    /**
     * @var bool
     */
    private $maintenanceModeInitialState;
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;
    /**
     * @var File
     */
    private $ioFile;

    /**
     * BackupHandler constructor.
     * @param MaintenanceMode $maintenanceMode
     * @param BackupRollbackFactory $backupRollbackFactory
     * @param ModuleDataSetupInterface $setup
     * @param File $ioFile
     */
    public function __construct(
        MaintenanceMode $maintenanceMode,
        BackupRollbackFactory $backupRollbackFactory,
        ModuleDataSetupInterface $setup,
        File $ioFile
    ) {
        $this->maintenanceMode = $maintenanceMode;
        $this->backupRollbackFactory = $backupRollbackFactory;
        $this->setup = $setup;
        $this->ioFile = $ioFile;
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        $this->maintenanceModeInitialState = $this->maintenanceMode->isOn();
        $this->maintenanceMode->set(true);
        $this->setup->getConnection()->beginTransaction();
    }

    /**
     * End database transaction
     */
    public function endTransaction()
    {
        $this->setup->getConnection()->rollBack();
        // disable maintenance only, if it has been disabled in the beginning
        if (!$this->maintenanceModeInitialState) {
            $this->maintenanceMode->set(false);

        }
    }

    /**
     * @param OutputInterface $output
     * @param string $backupPath
     * @throws \Exception
     */
    public function run(OutputInterface $output, $backupPath)
    {
        /*
         * Backup path
         */
        $backupPath = realpath($backupPath);
        if (!$this->ioFile->checkAndCreateFolder($backupPath, self::BACKUP_DIRECTORY_FILEMODE)) {
            throw new \RuntimeException(
                sprintf(
                    'Could not create backup folder: "%s"',
                    $backupPath
                )
            );
        }

        /*
         * Backup Handler
         */
        $backupHandler = $this->backupRollbackFactory->create($output);
        $backupFile = $backupHandler->dbBackup(time());

        $backupPath .= DIRECTORY_SEPARATOR . basename($backupFile);
        $backupPath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $backupPath);
        if ($backupFile !== $backupPath &&
            !$this->ioFile->cp($backupFile, $backupPath)) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to copy backup file "%s" to target "%s"',
                    $backupFile,
                    $backupPath
                )
            );
        }
    }
}
