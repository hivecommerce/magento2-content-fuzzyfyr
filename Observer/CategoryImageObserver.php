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

use AllInData\ContentFuzzyfyr\Handler\CategoryImageHandler;
use AllInData\ContentFuzzyfyr\Model\Configuration;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;

class CategoryImageObserver extends FuzzyfyrObserver
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
     * @var CategoryImageHandler
     */
    private $mediaFileHandler;

    /**
     * CategoryImageObserver constructor.
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryResourceFactory $categoryResourceFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     * @param CategoryImageHandler $mediaFileHandler
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryResourceFactory $categoryResourceFactory,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory,
        CategoryImageHandler $mediaFileHandler
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryResourceFactory = $categoryResourceFactory;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
        $this->mediaFileHandler = $mediaFileHandler;
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
            /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
            $urlRewrite->delete();
        }

        /*
         * Process
         */
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->load();
        foreach ($categoryCollection->getItems() as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
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
     * @param \Magento\Catalog\Model\Category $category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function doUpdate(Configuration $configuration, \Magento\Catalog\Model\Category $category)
    {
        if ($configuration->isUseOnlyEmpty() &&
            0 !== strlen(trim($category->getImageUrl()))
        ) {
            return;
        }

        $imagePath = $this->mediaFileHandler->getMediaCopyOfFile($configuration->getDummyImagePath());
        $category->setData('image', basename($imagePath));
    }
}
