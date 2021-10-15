<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Handler\Backup;

use HiveCommerce\ContentFuzzyfyr\Console\Command\ExportCommand;
use HiveCommerce\ContentFuzzyfyr\Handler\BackupHandler;
use Magento\Framework\Backup\Db\BackupFactory;
use Magento\Framework\EntityManager\EventManager;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Model\ConfigurationFactory;

/**
 * Class Db
 * @package HiveCommerce\ContentFuzzyfyr\Handler\Backup
 */
class DatabaseHandler extends \Magento\Framework\Backup\Db
{
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;
    /**
     * @var BackupHandler
     */
    private $backupHandler;

    /**
     * DatabaseHandler constructor.
     * @param BackupFactory $backupFactory
     * @param EventManager $eventManager
     * @param ConfigurationFactory $configurationFactory
     * @param BackupHandler $backupHandler
     */
    public function __construct(
        BackupFactory $backupFactory,
        EventManager $eventManager,
        ConfigurationFactory $configurationFactory,
        BackupHandler $backupHandler
    ) {
        parent::__construct($backupFactory);

        $this->eventManager = $eventManager;
        $this->configurationFactory = $configurationFactory;
        $this->backupHandler = $backupHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /*
         * Processing
         */
        $this->backupHandler->beginTransaction();

        try {
            $this->eventManager->dispatch(ExportCommand::EVENT_NAME, [
                'configuration' => $this->loadConfiguration()
            ]);

            if (!parent::create()) {
                throw new \RuntimeException('Failed to create database backup');
            }
        } catch (\Exception $e) {
            $this->backupHandler->endTransaction();
            return ExportCommand::ERROR_EXPORT_FAILED;
        }

        $this->backupHandler->endTransaction();

        return true;
    }

    /**
     * Add path that should be ignoring when creating or rolling back backup
     *
     * @param string|array $paths
     * @return $this
     */
    public function addIgnorePaths($paths)
    {
        return $this;
    }

    /**
     * @return Configuration
     */
    protected function loadConfiguration()
    {
        $configuration = $this->configurationFactory->create();
        // --- Flags
        $configuration->setApplyToUsers(true);
        $configuration->setApplyToCustomers(true);
        // --- Options
        $configuration->setDummyContentText(ExportCommand::DEFAULT_DUMMY_CONTENT_TEXT);
        $configuration->setDummyContentEmail(ExportCommand::DEFAULT_DUMMY_CONTENT_EMAIL);
        $configuration->setDummyContentUrl(ExportCommand::DEFAULT_DUMMY_CONTENT_URL);
        $configuration->setDummyPhoneNumber(ExportCommand::DEFAULT_DUMMY_CONTENT_PHONE);

        return $configuration;
    }
}
