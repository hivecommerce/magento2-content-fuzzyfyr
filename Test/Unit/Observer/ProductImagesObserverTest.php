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

use AllInData\ContentFuzzyfyr\Handler\MediaFileHandler;
use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Observer\ProductImagesObserver;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductImagesObserverTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Observer
 */
class ProductImagesObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
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

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects($this->never())
            ->method('getMediaCopyOfFile');

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $expectedImagePath = 'foobar';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $idx = 0;
        $product->expects($this->at($idx++))
            ->method('getMediaGalleryEntries')
            ->willReturn([]);
        $product->expects($this->at($idx++))
            ->method('setMediaGalleryEntries')
            ->With([]);
        $product->expects($this->at($idx++))
            ->method('addImageToMediaGallery')
            ->With($expectedImagePath, ['image', 'small_image', 'thumbnail'], false, false);
        $product->expects($this->once())
            ->method('save');

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
            ->willReturn(false);
        $configuration->expects($this->any())
            ->method('getDummyImagePath')
            ->willReturn($expectedImagePath);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects($this->once())
            ->method('getMediaCopyOfFile')
            ->willReturn($expectedImagePath);

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfullyWithOnlyEmptyFlag()
    {
        $expectedImagePath = 'foobar';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $idx = 0;
        $product->expects($this->at($idx++))
            ->method('getMediaGalleryEntries')
            ->willReturn([]);
        $product->expects($this->at($idx++))
            ->method('addImageToMediaGallery')
            ->With($expectedImagePath, ['image', 'small_image', 'thumbnail'], false, false);
        $product->expects($this->once())
            ->method('save');

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
            ->method('getDummyImagePath')
            ->willReturn($expectedImagePath);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects($this->once())
            ->method('getMediaCopyOfFile')
            ->willReturn($expectedImagePath);

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfullyWithOnlyEmptyFlagAndNonEmptyGallery()
    {
        $expectedImagePath = 'foobar';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $idx = 0;
        $product->expects($this->at($idx++))
            ->method('getMediaGalleryEntries')
            ->willReturn(['foo' => 'bar']);
        $product->expects($this->once())
            ->method('save');

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
            ->method('getDummyImagePath')
            ->willReturn($expectedImagePath);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $mediaFileHandler = $this->getMediaFileHandler();
        $mediaFileHandler->expects($this->never())
            ->method('getMediaCopyOfFile');

        $observer = new ProductImagesObserver($productCollectionFactory, $mediaFileHandler);

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
     * @return MockObject|\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private function getProductCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Catalog\Model\ResourceModel\Product\Collection', $instance);
    }

    /**
     * @return MockObject|MediaMediaFileHandlerHandler
     */
    private function getMediaFileHandler()
    {
        return $this->getMockBuilder(MediaFileHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
