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
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;
use Magento\Framework\Event\ObserverInterface;

class CategoriesObserver implements ObserverInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;
    /**
     * @var CategoryResourceFactory
     */
    protected $categoryResourceFactory;

    /**
     * CategorysObserver constructor.
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryResourceFactory $categoryResourceFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory, CategoryResourceFactory $categoryResourceFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryResourceFactory = $categoryResourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToCategories()) {
            return;
        }

        // @TODO clear table url_rewrite for entity_type category
        // @TODO mark indexer to invalidate index

        /** @var CategoryResource $categoryResource */
        $categoryResource = $this->categoryResourceFactory->create();

        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->load();
        foreach ($categoryCollection->getItems() as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $this->updateData($configuration, $category);
            $categoryResource->save($category);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     */
    protected function updateData(Configuration $configuration, \Magento\Catalog\Model\Category $category)
    {
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($category->getDescription()))) {
            $category->setDescription($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($category->getMetaTitle()))) {
            $category->setMetaTitle($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($category->getMetaKeywords()))) {
            $category->setMetaKeywords($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($category->getMetaDescription()))) {
            $category->setMetaDescription($configuration->getDummyContentText());
        }

        return $category;
    }
}
