<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Handler;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class BackupHandler
 * @package AllInData\ContentFuzzyfyr\Handler
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
     * @var DeploymentConfig
     */
    private $deploymentConfig;
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
     * @param DeploymentConfig $deploymentConfig
     * @param ModuleDataSetupInterface $setup
     * @param File $ioFile
     */
    public function __construct(
        MaintenanceMode $maintenanceMode,
        BackupRollbackFactory $backupRollbackFactory,
        DeploymentConfig $deploymentConfig,
        ModuleDataSetupInterface $setup,
        File $ioFile
    ) {
        $this->maintenanceMode = $maintenanceMode;
        $this->backupRollbackFactory = $backupRollbackFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->setup = $setup;
        $this->ioFile = $ioFile;
        $this->maintenanceModeInitialState = $this->maintenanceMode->isOn();
    }

    /**
     * Begin database transaction
     */
    public function beginTransaction()
    {
        $this->maintenanceMode->set(true);
        $this->setup->getConnection()->beginTransaction();
    }

    /**
     * End database transaction
     */
    public function endTransaction()
    {
        $this->setup->getConnection()->rollBack();
        $this->maintenanceMode->set(false);
    }

    /**
     * @param string $backupPath
     */
    public function run($backupPath)
    {
        /*
         * Backup path
         */
        $backupPath = realpath($backupPath);
        if (!$backupPath) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not resolve backup path: "%s"',
                    $backupPath
                )
            );
        }

        if (!$this->ioFile->mkdir($backupPath, self::BACKUP_DIRECTORY_FILEMODE, true)) {
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
        $backupHandler = $this->backupRollbackFactory->create();
        $backupFile = $backupHandler->dbBackup(time());

        $backupPath .= DIRECTORY_SEPARATOR . basename($backupFile);
        $backupPath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $backupPath);
        if (!$this->ioFile->cp($backupFile, $backupPath)) {
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
