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
use HiveCommerce\ContentFuzzyfyr\Observer\CategoriesObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CategoriesObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class CategoriesObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $urlRewriteCollectionFactory = $this->getUrlRewriteCollectionFactory();
        $urlRewriteCollectionFactory->expects(self::never())
            ->method('create');

        $categoryResourceFactory = $this->getCategoryResourceFactory();
        $categoryResourceFactory->expects(self::never())
            ->method('create');

        $categoryCollectionFactory = $this->getCategoryCollectionFactory();
        $categoryCollectionFactory->expects(self::never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToCategories')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
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
        $urlRewrite->expects(self::once())
            ->method('delete');

        $urlRewriteCollection = $this->getMockBuilder(UrlRewriteCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlRewriteCollection->expects(self::once())
            ->method('addFieldToFilter')
            ->with('entity_type', ['eq' => 'category'])
            ->willReturnSelf();
        $urlRewriteCollection->expects(self::once())
            ->method('load');
        $urlRewriteCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$urlRewrite]);

        $urlRewriteCollectionFactory = $this->getUrlRewriteCollectionFactory($urlRewriteCollection);

        $rootCategory = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rootCategory->expects(self::once())
            ->method('getId')
            ->willReturn(CategoriesObserver::ROOT_CATEGORY_ID);

        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $categoryResource = $this->getMockBuilder(CategoryResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryResource->expects(self::once())
            ->method('save')
            ->with($category);
        $categoryResourceFactory = $this->getCategoryResourceFactory($categoryResource);

        $categoryCollection = $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollection->expects(self::once())
            ->method('load');
        $categoryCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$rootCategory, $category]);
        $categoryCollectionFactory = $this->getCategoryCollectionFactory($categoryCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToCategories')
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
