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
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Cms\Model\ResourceModel\PageFactory as PageResourceFactory;
use Magento\Framework\Event\ObserverInterface;

class CmsPagesObserver implements ObserverInterface
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
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToCmsPages()) {
            return;
        }

        /** @var PageResource $pageResource */
        $pageResource = $this->pageResourceFactory->create();

        /** @var PageCollection $pageCollection */
        $pageCollection = $this->pageCollectionFactory->create();
        $pageCollection->load();
        foreach ($pageCollection->getItems() as $page) {
            /** @var \Magento\Cms\Model\Page $page */
            $this->updateData($configuration, $page);
            $pageResource->save($page);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Cms\Model\Page $page
     * @return \Magento\Cms\Model\Page
     */
    protected function updateData(Configuration $configuration, \Magento\Cms\Model\Page $page)
    {
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($page->getContent()))) {
            $page->setContent($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($page->getContentHeading()))) {
            $page->setContentHeading($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($page->getMetaTitle()))) {
            $page->setMetaTitle($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($page->getMetaKeywords()))) {
            $page->setMetaKeywords($configuration->getDummyContentText());
        }
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($page->getMetaDescription()))) {
            $page->setMetaDescription($configuration->getDummyContentText());
        }

        return $page;
    }
}
