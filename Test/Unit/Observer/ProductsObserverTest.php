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
use HiveCommerce\ContentFuzzyfyr\Observer\ProductsObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductsObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class ProductsObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully(): void
    {
        $productResourceFactory = $this->getProductResourceFactory();
        $productResourceFactory->expects(self::never())
            ->method('create');

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionFactory = $this->getProductCollectionFactory($productCollection);
        $productCollectionFactory->expects(self::never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToProducts')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new ProductsObserver($productCollectionFactory, $productResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productResource = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productResource->expects(self::once())
            ->method('save')
            ->with($product);
        $productResourceFactory = $this->getProductResourceFactory($productResource);

        $productCollection = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects(self::once())
            ->method('load');
        $productCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$product]);
        $productCollectionFactory = $this->getProductCollectionFactory($productCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToProducts')
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


        $observer = new ProductsObserver($productCollectionFactory, $productResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @return MockObject&Observer
     */
    private function getObserver()
    {
        return $this->createMock(Observer::class);
    }

    /**
     * @return MockObject&Configuration
     */
    private function getConfiguration()
    {
        return $this->createMock(Configuration::class);
    }

    /**
     * @param MockObject $instance
     * @return MockObject&\Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    private function getProductResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Product', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject&\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private function getProductCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Product\Collection', $instance);
    }
}
