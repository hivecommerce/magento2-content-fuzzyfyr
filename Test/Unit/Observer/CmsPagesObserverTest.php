<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
class PagesObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $pageResourceFactory = $this->getPageResourceFactory();
        $pageResourceFactory->expects($this->never())
            ->method('create');

        $pageCollectionFactory = $this->getPageCollectionFactory();
        $pageCollectionFactory->expects($this->never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToCmsPages')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
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
        $idx = 0;
        $page->expects($this->at($idx++))
            ->method('getData')
            ->With('content')
            ->willReturn(null);
        $page->expects($this->at($idx++))
            ->method('setData')
            ->with('content', 'dummy-text');
        $page->expects($this->at($idx++))
            ->method('getData')
            ->With('content_heading')
            ->willReturn(null);
        $page->expects($this->at($idx++))
            ->method('setData')
            ->with('content_heading', 'dummy-text');
        $page->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_title')
            ->willReturn(null);
        $page->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_title', 'dummy-text');
        $page->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_keywords')
            ->willReturn(null);
        $page->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_keywords', 'dummy-text');
        $page->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_description')
            ->willReturn(null);
        $page->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_description', 'dummy-text');

        $pageResource = $this->getMockBuilder(PageResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageResource->expects($this->once())
            ->method('save')
            ->with($page);
        $pageResourceFactory = $this->getPageResourceFactory($pageResource);

        $pageCollection = $this->getMockBuilder(PageCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pageCollection->expects($this->once())
            ->method('load');
        $pageCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$page]);
        $pageCollectionFactory = $this->getPageCollectionFactory($pageCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToCmsPages')
            ->willReturn(true);
        $configuration->expects($this->any())
            ->method('isUseOnlyEmpty')
            ->willReturn(true);
        $configuration->expects($this->any())
            ->method('getDummyContentText')
            ->willReturn('dummy-text');
        $configuration->expects($this->any())
            ->method('getDummyContentEmail')
            ->willReturn('dummy-email');

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
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
