<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Observer;

use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Observer\FuzzyfyrObserver;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FuzzyObserverTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Observer
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
        $this->assertTrue($method->invokeArgs($observer, [$configuration]));
    }
}