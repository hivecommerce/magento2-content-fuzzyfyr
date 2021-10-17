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

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\UrlRewrite;

class CategoriesObserver extends FuzzyfyrObserver
{
    /*
     * Root Category
     */
    const ROOT_CATEGORY_ID = "1";

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;
    /**
     * @var CategoryResourceFactory
     */
    protected $categoryResourceFactory;
    /**
     * @var UrlRewriteCollectionFactory
     */
    protected $urlRewriteCollectionFactory;

    /**
     * CategoriesObserver constructor.
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryResourceFactory $categoryResourceFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryResourceFactory $categoryResourceFactory,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryResourceFactory = $categoryResourceFactory;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Configuration $configuration): bool
    {
        return $configuration->isApplyToCategories();
    }

    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var CategoryResource $categoryResource */
        $categoryResource = $this->categoryResourceFactory->create();

        /*
         * clear table url_rewrite for entity_type category
         */
        /** @var UrlRewriteCollection $urlRewriteCollection */
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $urlRewriteCollection
            ->addFieldToFilter('entity_type', ['eq' => 'category'])
            ->load();
        foreach ($urlRewriteCollection->getItems() as $urlRewrite) {
            /** @var UrlRewrite $urlRewrite */
            $urlRewrite->delete();
        }

        /*
         * Process
         */
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->load();
        foreach ($categoryCollection->getItems() as $category) {
            /** @var Category $category */
            if (self::ROOT_CATEGORY_ID === $category->getId()) {
                // skip root category
                continue;
            }
            $category->load($category->getId());
            $this->doUpdate($configuration, $category);
            $categoryResource->save($category);
        }
    }

    /**
     * @param Configuration $configuration
     * @param Category $category
     * @return void
     */
    protected function doUpdate(Configuration $configuration, Category $category): void
    {
        $this->updateData($category, 'description', $configuration, $configuration->getDummyContentText());
        $this->updateData($category, 'meta_title', $configuration, $configuration->getDummyContentText());
        $this->updateData($category, 'meta_keywords', $configuration, $configuration->getDummyContentText());
        $this->updateData($category, 'meta_description', $configuration, $configuration->getDummyContentText());
    }
}
