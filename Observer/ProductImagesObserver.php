<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Observer;

use AllInData\ContentFuzzyfyr\Handler\MediaFileHandler;
use AllInData\ContentFuzzyfyr\Model\Configuration;
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
    public function isValid(Configuration $configuration)
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
            /** @var \Magento\Catalog\Model\Product $product */
            $this->doUpdate($configuration, $product);
            $product->save();
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Product $product
     * @throws \Exception
     */
    protected function doUpdate(Configuration $configuration, \Magento\Catalog\Model\Product $product)
    {
        $mediaGalleryEntries = $product->getMediaGalleryEntries();

        if ($configuration->isUseOnlyEmpty() && !empty($mediaGalleryEntries)) {
            return;
        }

        if (!$configuration->isUseOnlyEmpty()) {
            $product->setMediaGalleryEntries([]);
        }

        $imagePath = $this->mediaFileHandler->getMediaCopyOfFile($configuration->getDummyImagePath());
        $product->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], false, false);
    }
}
