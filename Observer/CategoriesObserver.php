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

class CategoriesObserver extends FuzzyfyrObserver
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
    public function isValid(Configuration $configuration)
    {
        return $configuration->isApplyToCategories();
    }

    /**
     * {@inheritdoc}
     * @TODO clear table url_rewrite for entity_type category
     * @TODO mark indexer to invalidate index
     */
    protected function run(Configuration $configuration)
    {
        /** @var CategoryResource $categoryResource */
        $categoryResource = $this->categoryResourceFactory->create();

        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->load();
        foreach ($categoryCollection->getItems() as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $this->doUpdate($configuration, $category);
            $categoryResource->save($category);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Catalog\Model\Category $category
     */
    protected function doUpdate(Configuration $configuration, \Magento\Catalog\Model\Category $category)
    {
        $this->updateData($category, 'description', $configuration, $configuration->getDummyContentText());
        $this->updateData($category, 'meta_title', $configuration, $configuration->getDummyContentText());
        $this->updateData($category, 'meta_keywords', $configuration, $configuration->getDummyContentText());
        $this->updateData($category, 'meta_description', $configuration, $configuration->getDummyContentText());
    }
}
