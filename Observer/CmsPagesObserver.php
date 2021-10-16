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
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Cms\Model\ResourceModel\PageFactory as PageResourceFactory;

class CmsPagesObserver extends FuzzyfyrObserver
{
    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;
    /**
     * @var PageResourceFactory
     */
    protected $pageResourceFactory;

    /**
     * PagesObserver constructor.
     * @param PageCollectionFactory $pageCollectionFactory
     * @param PageResourceFactory $pageResourceFactory
     */
    public function __construct(PageCollectionFactory $pageCollectionFactory, PageResourceFactory $pageResourceFactory)
    {
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->pageResourceFactory = $pageResourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Configuration $configuration)
    {
        return $configuration->isApplyToCmsPages();
    }

    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var PageResource $pageResource */
        $pageResource = $this->pageResourceFactory->create();

        /** @var PageCollection $pageCollection */
        $pageCollection = $this->pageCollectionFactory->create();
        $pageCollection->load();
        foreach ($pageCollection->getItems() as $page) {
            /** @var \Magento\Cms\Model\Page $page */
            $this->doUpdate($configuration, $page);
            $pageResource->save($page);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Cms\Model\Page $page
     */
    protected function doUpdate(Configuration $configuration, \Magento\Cms\Model\Page $page)
    {
        $this->updateData($page, 'content', $configuration, $configuration->getDummyContentText());
        $this->updateData($page, 'content_heading', $configuration, $configuration->getDummyContentText());
        $this->updateData($page, 'meta_title', $configuration, $configuration->getDummyContentText());
        $this->updateData($page, 'meta_keywords', $configuration, $configuration->getDummyContentText());
        $this->updateData($page, 'meta_description', $configuration, $configuration->getDummyContentText());
    }
}
