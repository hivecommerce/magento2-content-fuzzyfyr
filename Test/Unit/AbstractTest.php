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

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @template T
     * @param class-string<T> $className
     * @param array $arguments
     * @return T
     */
    public function getTestEntity(string $className, array $arguments = [])
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        return $objectManager->getObject($className, $arguments);
    }

    /**
     * @param class-string $instanceName
     * @param MockObject|null $mock
     * @return mixed
     */
    public function getFactoryAsMock(string $instanceName, MockObject $mock = null)
    {
        /**
         * @var class-string $factoryFullName
         */
        $factoryFullName = $instanceName . 'Factory';
        $parts = explode('\\', $factoryFullName);

        if ($mock === null) {
            $mock = $this->getMockBuilder($instanceName)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $factory = $this->getMockBuilder($factoryFullName)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $factory->expects(self::any())
            ->method('create')
            ->willReturn($mock);

        return $factory;
    }
}
