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

namespace HiveCommerce\ContentFuzzyfyr\Observer;

use Exception;
use HiveCommerce\ContentFuzzyfyr\Handler\MediaFileHandler;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class ProductImagesObserver extends FuzzyfyrObserver
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var MediaFileHandler
     */
    private $mediaFileHandler;

    /**
     * ProductImagesObserver constructor.
     * @param ProductCollectionFactory $productCollectionFactory
     * @param MediaFileHandler $mediaFileHandler
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        MediaFileHandler $mediaFileHandler
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->mediaFileHandler = $mediaFileHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Configuration $configuration): bool
    {
        return $configuration->isApplyToProducts();
    }

    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->load();
        foreach ($productCollection->getItems() as $product) {
            /** @var Product $product */
            $this->doUpdate($configuration, $product);
            $product->save();
        }
    }

    /**
     * @param Configuration $configuration
     * @param Product $product
     * @return void
     * @throws Exception
     */
    protected function doUpdate(Configuration $configuration, Product $product): void
    {
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        if ($mediaGalleryEntries === null) {
            $mediaGalleryEntries = [];
        }

        if ($configuration->isUseOnlyEmpty() && (count($mediaGalleryEntries) >0)) {
            return;
        }

        if (!$configuration->isUseOnlyEmpty()) {
            $product->setMediaGalleryEntries([]);
        }

        $imagePath = $this->mediaFileHandler->getMediaCopyOfFile($configuration->getDummyImagePath());
        $product->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], false, false);
    }
}
