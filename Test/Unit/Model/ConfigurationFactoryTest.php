<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Model;

use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Model\ConfigurationFactory;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ConfigurationFactoryTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Model
 */
class ConfigurationFactoryTest extends AbstractTest
{
    /**
     * @test
     */
    public function createEntitySuccessfully()
    {
        $expectedEntity = $this->createMock(Configuration::class);

        $om = $this->getObjectManager();
        $om->expects($this->once())
            ->method('create')
            ->with('\\AllInData\\ContentFuzzyfyr\\Model\\Configuration', ['foo' => 'bar'])
            ->willReturn($expectedEntity);

        $factory = new ConfigurationFactory($om);

        $this->assertEquals($expectedEntity, $factory->create(['foo' => 'bar']));
    }

    /**
     * @return MockObject|ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return $this->createMock(ObjectManagerInterface::class);
    }
}