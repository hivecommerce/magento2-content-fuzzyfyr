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
use Magento\Framework\Event\ObserverInterface;

class ProductsObserver implements ObserverInterface
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
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToProducts()) {
            return;
        }

        /** @var ProductResource $productResource */
        $productResource = $this->productResourceFactory->create();

        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->load();
        foreach ($productCollection->getItems() as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $this->updateData($configuration, $product);
            $productResource->save($product);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    protected function updateData(Configuration $configuration, \Magento\Catalog\Model\Product $product)
    {
        $product->setDescription($configuration->getDummyContentText());
        $product->setShortDescription($configuration->getDummyContentText());
        $product->setMetaTitle($configuration->getDummyContentText());
        $product->setMetaKeyword($configuration->getDummyContentText());
        $product->setMetaDescription($configuration->getDummyContentText());

        return $product;
    }
}
