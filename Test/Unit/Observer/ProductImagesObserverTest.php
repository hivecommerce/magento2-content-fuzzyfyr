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

use HiveCommerce\ContentFuzzyfyr\Handler\MediaFileHandler;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Observer\ProductImagesObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductImagesObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class ProductImagesObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully(): void
    {
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

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects(self::never())
            ->method('getMediaCopyOfFile');

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully(): void
    {
        $expectedImagePath = 'foobar';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects(self::once())
            ->method('save');

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
            ->willReturn(false);
        $configuration->expects(self::any())
            ->method('getDummyImagePath')
            ->willReturn($expectedImagePath);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects(self::once())
            ->method('getMediaCopyOfFile')
            ->willReturn($expectedImagePath);

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfullyWithOnlyEmptyFlag(): void
    {
        $expectedImagePath = 'foobar';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $idx = 0;
        $product->expects(self::once())
            ->method('getMediaGalleryEntries')
            ->willReturn([]);
        $product->expects(self::once())
            ->method('addImageToMediaGallery')
            ->with($expectedImagePath, ['image', 'small_image', 'thumbnail'], false, false);
        $product->expects(self::once())
            ->method('save');

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
            ->method('getDummyImagePath')
            ->willReturn($expectedImagePath);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects(self::once())
            ->method('getMediaCopyOfFile')
            ->willReturn($expectedImagePath);

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfullyWithOnlyEmptyFlagAndNonEmptyGallery(): void
    {
        $expectedImagePath = 'foobar';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects(self::once())
            ->method('getMediaGalleryEntries')
            ->willReturn(['foo' => 'bar']);
        $product->expects(self::once())
            ->method('save');

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
            ->method('getDummyImagePath')
            ->willReturn($expectedImagePath);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects(self::never())
            ->method('getMediaCopyOfFile');

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

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
     * @return MockObject&\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private function getProductCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Product\Collection', $instance);
    }

    /**
     * @return MockObject&MediaFileHandler
     */
    private function getMediaFileHandler()
    {
        return $this->getMockBuilder(MediaFileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
