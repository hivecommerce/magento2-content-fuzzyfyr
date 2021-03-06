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

namespace HiveCommerce\ContentFuzzyfyr\Model\Backup;

use HiveCommerce\ContentFuzzyfyr\Handler\Backup\DatabaseHandler;
use Magento\Framework\Backup\BackupInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package HiveCommerce\ContentFuzzyfyr\Model\Backup
 */
class Factory extends \Magento\Framework\Backup\Factory
{
    /*
     * Types
     */
    const TYPE_GDPR_DB = 'hc_content_export_db';

    /**
     * @var DatabaseHandler
     */
    protected $dbBackupHandler;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DatabaseHandler $dbBackupHandler
     */
    public function __construct(ObjectManagerInterface $objectManager, DatabaseHandler $dbBackupHandler)
    {
        parent::__construct($objectManager);

        $this->_allowedTypes = array_merge($this->_allowedTypes, [
            self::TYPE_GDPR_DB
        ]);

        $this->dbBackupHandler = $dbBackupHandler;
    }

    /**
     * Create new backup instance
     *
     * @param string $type
     * @return BackupInterface
     * @throws LocalizedException
     */
    public function create($type)
    {
        switch ($type) {
            case self::TYPE_GDPR_DB:
                return $this->dbBackupHandler;
        }

        return parent::create($type);
    }
}
