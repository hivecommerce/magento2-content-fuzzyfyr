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
use AllInData\ContentFuzzyfyr\Observer\ProductsObserver;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductsObserverTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Observer
 */
class ProductsObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $productResourceFactory = $this->getProductResourceFactory();
        $productResourceFactory->expects($this->never())
            ->method('create');

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionFactory = $this->getProductCollectionFactory($productCollection);
        $productCollectionFactory->expects($this->never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToProducts')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new ProductsObserver($productCollectionFactory, $productResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $idx = 0;
        $product->expects($this->at($idx++))
            ->method('getData')
            ->With('description')
            ->willReturn(null);
        $product->expects($this->at($idx++))
            ->method('setData')
            ->with('description', 'dummy-text');
        $product->expects($this->at($idx++))
            ->method('getData')
            ->With('short_description')
            ->willReturn(null);
        $product->expects($this->at($idx++))
            ->method('setData')
            ->with('short_description', 'dummy-text');
        $product->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_title')
            ->willReturn(null);
        $product->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_title', 'dummy-text');
        $product->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_keyword')
            ->willReturn(null);
        $product->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_keyword', 'dummy-text');
        $product->expects($this->at($idx++))
            ->method('getData')
            ->With('meta_description')
            ->willReturn(null);
        $product->expects($this->at($idx++))
            ->method('setData')
            ->with('meta_description', 'dummy-text');

        $productResource = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productResource->expects($this->once())
            ->method('save')
            ->with($product);
        $productResourceFactory = $this->getProductResourceFactory($productResource);

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->once())
            ->method('load');
        $productCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$product]);
        $productCollectionFactory = $this->getProductCollectionFactory($productCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToProducts')
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


        $observer = new ProductsObserver($productCollectionFactory, $productResourceFactory);

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
     * @return MockObject|\Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    private function getProductResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Product', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private function getProductCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Product\Collection', $instance);
    }
}