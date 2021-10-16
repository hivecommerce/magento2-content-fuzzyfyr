<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Model;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Model\ConfigurationFactory;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ConfigurationFactoryTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Model
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
        $om->expects(self::once())
            ->method('create')
            ->with('\\HiveCommerce\\ContentFuzzyfyr\\Model\\Configuration', ['foo' => 'bar'])
            ->willReturn($expectedEntity);

        $factory = new ConfigurationFactory($om);

        self::assertEquals($expectedEntity, $factory->create(['foo' => 'bar']));
    }

    /**
     * @return MockObject|ObjectManagerInterface
     */
    private function getObjectManager()
    {
        return $this->createMock(ObjectManagerInterface::class);
    }
}
