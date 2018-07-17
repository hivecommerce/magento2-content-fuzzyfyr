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
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\CategoryFactory as CategoryResourceFactory;

class CategoryImageObserver extends FuzzyfyrObserver
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
     * @var MediaFileHandler
     */
    private $mediaFileHandler;

    /**
     * CategoryImageObserver constructor.
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryResourceFactory $categoryResourceFactory
     * @param MediaFileHandler $mediaFileHandler
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryResourceFactory $categoryResourceFactory,
        MediaFileHandler $mediaFileHandler
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryResourceFactory = $categoryResourceFactory;
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
        $category->setData('image', $imagePath);
    }
}
