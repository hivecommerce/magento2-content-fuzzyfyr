<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Model\Backup;

use HiveCommerce\ContentFuzzyfyr\Handler\Backup\DatabaseHandler;
use Magento\Framework\Backup\BackupInterface;

/**
 * Class Factory
 * @package HiveCommerce\ContentFuzzyfyr\Model\Backup
 */
class Factory extends \Magento\Framework\Backup\Factory
{
    /*
     * Types
     */
    const TYPE_GDPR_DB = 'aid_content_export_db';

    /**
     * @var DatabaseHandler
     */
    protected $dbBackupHandler;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param DatabaseHandler $dbBackupHandler
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, DatabaseHandler $dbBackupHandler)
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
