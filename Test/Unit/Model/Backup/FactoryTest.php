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

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Model\Backup;

use HiveCommerce\ContentFuzzyfyr\Model\Backup\Factory;
use HiveCommerce\ContentFuzzyfyr\Handler\Backup\DatabaseHandler;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Backup\BackupInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FactoryTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Model
 */
class FactoryTest extends AbstractTest
{
    /**
     * @test
     */
    public function createEntitySuccessfully(): void
    {
        $databaseHandler = $this->getDatabaseHandler();
        $backupEntity = $this->createMock(BackupInterface::class);

        $om = $this->getObjectManager();
        $om->expects(self::once())
            ->method('create')
            ->with('Magento\Framework\Backup\Filesystem')
            ->willReturn($backupEntity);

        $factory = new Factory($om, $databaseHandler);

        self::assertEquals($databaseHandler, $factory->create(Factory::TYPE_GDPR_DB));
        self::assertEquals($backupEntity, $factory->create(Factory::TYPE_FILESYSTEM));
    }

    /**
     * @return MockObject&ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return $this->createMock(ObjectManagerInterface::class);
    }

    /**
     * @return MockObject&DatabaseHandler
     */
    private function getDatabaseHandler()
    {
        return $this->getMockBuilder(DatabaseHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
