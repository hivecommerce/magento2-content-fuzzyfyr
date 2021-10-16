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
use HiveCommerce\ContentFuzzyfyr\Observer\CmsPagesObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PagesObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class CmsPagesObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $pageResourceFactory = $this->getPageResourceFactory();
        $pageResourceFactory->expects(self::never())
            ->method('create');

        $pageCollectionFactory = $this->getPageCollectionFactory();
        $pageCollectionFactory->expects(self::never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToCmsPages')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new CmsPagesObserver($pageCollectionFactory, $pageResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageResource = $this->getMockBuilder(PageResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageResource->expects(self::once())
            ->method('save')
            ->with($page);
        $pageResourceFactory = $this->getPageResourceFactory($pageResource);

        $pageCollection = $this->getMockBuilder(PageCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageCollection->expects(self::once())
            ->method('load');
        $pageCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$page]);
        $pageCollectionFactory = $this->getPageCollectionFactory($pageCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToCmsPages')
            ->willReturn(true);
        $configuration->expects(self::any())
            ->method('isUseOnlyEmpty')
            ->willReturn(true);
        $configuration->expects(self::any())
            ->method('getDummyContentText')
            ->willReturn('dummy-text');
        $configuration->expects(self::any())
            ->method('getDummyContentEmail')
            ->willReturn('dummy-email');

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);


        $observer = new CmsPagesObserver($pageCollectionFactory, $pageResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @return MockObject|Observer
     */
    private function getObserver()
    {
        return $this->createMock(Observer::class);
    }

    /**
     * @return MockObject|Configuration
     */
    private function getConfiguration()
    {
        return $this->createMock(Configuration::class);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Cms\Model\ResourceModel\PageFactory
     */
    private function getPageResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Cms\Model\ResourceModel\Page', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    private function getPageCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Cms\Model\ResourceModel\Page\Collection', $instance);
    }
}
