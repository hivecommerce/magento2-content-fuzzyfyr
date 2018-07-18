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
use AllInData\ContentFuzzyfyr\Observer\CategoriesObserver;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CategoriesObserverTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Observer
 */
class CategoriesObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $urlRewriteCollectionFactory = $this->getUrlRewriteCollectionFactory();
        $urlRewriteCollectionFactory->expects($this->never())
            ->method('create');

        $categoryResourceFactory = $this->getCategoryResourceFactory();
        $categoryResourceFactory->expects($this->never())
            ->method('create');

        $categoryCollectionFactory = $this->getCategoryCollectionFactory();
        $categoryCollectionFactory->expects($this->never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToCategories')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new CategoriesObserver($categoryCollectionFactory, $categoryResourceFactory, $urlRewriteCollectionFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $urlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Model\UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewrite->expects($this->once())
            ->method('delete');

        $urlRewriteCollection = $this->getMockBuilder(UrlRewriteCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewriteCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_type', ['eq' => 'category'])
            ->willReturnSelf();
        $urlRewriteCollection->expects($this->once())
            ->method('load');
        $urlRewriteCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$urlRewrite]);

        $urlRewriteCollectionFactory = $this->getUrlRewriteCollectionFactory($urlRewriteCollection);

        $rootCategory = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootCategory->expects($this->once())
            ->method('getId')
            ->willReturn(CategoriesObserver::ROOT_CATEGORY_ID);

        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $idx = 0;
        $category->expects($this->at($idx++))
            ->method('getId')
            ->willReturn(42);
        $category->expects($this->at($idx++))
            ->method('getId')
            ->willReturn(42);
        $category->expects($this->at($idx++))
            ->method('load')
            ->with(42);
        $category->expects($this->at($idx++))
            ->method('getData')
            ->With('description')
            ->willReturn(null);
        $category->expects($this->at($idx++))
            ->method('setData')
            ->with('description', 'dummy-text');
        $category->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_title')
            ->willReturn(null);
        $category->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_title', 'dummy-text');
        $category->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_keywords')
            ->willReturn(null);
        $category->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_keywords', 'dummy-text');
        $category->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_description')
            ->willReturn(null);
        $category->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_description', 'dummy-text');

        $categoryResource = $this->getMockBuilder(CategoryResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryResource->expects($this->once())
            ->method('save')
            ->with($category);
        $categoryResourceFactory = $this->getCategoryResourceFactory($categoryResource);

        $categoryCollection = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollection->expects($this->once())
            ->method('load');
        $categoryCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$rootCategory, $category]);
        $categoryCollectionFactory = $this->getCategoryCollectionFactory($categoryCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToCategories')
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


        $observer = new CategoriesObserver($categoryCollectionFactory, $categoryResourceFactory, $urlRewriteCollectionFactory);

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
     * @return MockObject|\Magento\Catalog\Model\ResourceModel\CategoryFactory
     */
    private function getCategoryResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Category', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private function getCategoryCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Category\Collection', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
     */
    private function getUrlRewriteCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection', $instance);
    }
}