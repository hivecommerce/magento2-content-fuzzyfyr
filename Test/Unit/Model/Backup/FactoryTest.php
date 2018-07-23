<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Model\Backup;

use AllInData\ContentFuzzyfyr\Model\Backup\Factory;
use AllInData\ContentFuzzyfyr\Handler\Backup\DatabaseHandler;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Backup\BackupInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FactoryTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Model
 */
class FactoryTest extends AbstractTest
{
    /**
     * @test
     */
    public function createEntitySuccessfully()
    {
        $databaseHandler = $this->getDatabaseHandler();
        $backupEntity = $this->createMock(BackupInterface::class);

        $om = $this->getObjectManager();
        $om->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\Backup\Filesystem')
            ->willReturn($backupEntity);

        $factory = new Factory($om, $databaseHandler);

        $this->assertEquals($databaseHandler, $factory->create(Factory::TYPE_GDPR_DB));
        $this->assertEquals($backupEntity, $factory->create(Factory::TYPE_FILESYSTEM));
    }

    /**
     * @return MockObject|ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return $this->createMock(ObjectManagerInterface::class);
    }

    /**
     * @return MockObject|DatabaseHandler
     */
    private function getDatabaseHandler()
    {
        return $this->getMockBuilder(DatabaseHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}