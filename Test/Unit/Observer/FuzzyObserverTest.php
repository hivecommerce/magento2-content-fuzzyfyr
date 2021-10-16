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

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Observer\FuzzyfyrObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FuzzyObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class FuzzyObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function validateDefaultSuccessfully()
    {
        $configuration = $this->createMock(Configuration::class);

        /** @var FuzzyfyrObserver|MockObject $observer */
        $observer = $this->getMockForAbstractClass(FuzzyfyrObserver::class);
        $class = new \ReflectionClass(FuzzyfyrObserver::class);
        $method = $class->getMethod('isValid');
        $method->setAccessible(true);
        self::assertTrue($method->invokeArgs($observer, [$configuration]));
    }
}
