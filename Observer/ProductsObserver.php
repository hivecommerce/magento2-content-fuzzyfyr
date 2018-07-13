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

use AllInData\ContentFuzzyfyr\Model\Configuration;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;

class ProductsObserver extends FuzzyfyrObserver
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var ProductResourceFactory
     */
    protected $productResourceFactory;

    /**
     * ProductsObserver constructor.
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductResourceFactory $productResourceFactory
     */
    public function __construct(ProductCollectionFactory $productCollectionFactory, ProductResourceFactory $productResourceFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productResourceFactory = $productResourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Configuration $configuration)
    {
        return $configuration->isApplyToCategories();
    }

    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var ProductResource $productResource */
        $productResource = $this->productResourceFactory->create();

        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->load();
        foreach ($productCollection->getItems() as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $this->doUpdate($configuration, $product);
            $productResource->save($product);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function doUpdate(Configuration $configuration, \Magento\Catalog\Model\Product $product)
    {
        $this->updateData($product, 'description', $configuration, $configuration->getDummyContentText());
        $this->updateData($product, 'short_description', $configuration, $configuration->getDummyContentText());
        $this->updateData($product, 'meta_title', $configuration, $configuration->getDummyContentText());
        $this->updateData($product, 'meta_keyword', $configuration, $configuration->getDummyContentText());
        $this->updateData($product, 'meta_description', $configuration, $configuration->getDummyContentText());
    }
}
