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
use Magento\Cms\Model\ResourceModel\Block\Collection as BlockCollection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Cms\Model\ResourceModel\BlockFactory as BlockResourceFactory;
use Magento\Framework\Event\ObserverInterface;

class CmsBlocksObserver implements ObserverInterface
{
    /**
     * @var BlockCollectionFactory
     */
    protected $blockCollectionFactory;
    /**
     * @var BlockResourceFactory
     */
    protected $blockResourceFactory;

    /**
     * BlocksObserver constructor.
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param BlockResourceFactory $blockResourceFactory
     */
    public function __construct(BlockCollectionFactory $blockCollectionFactory, BlockResourceFactory $blockResourceFactory)
    {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockResourceFactory = $blockResourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToCmsBlocks()) {
            return;
        }

        /** @var BlockResource $blockResource */
        $blockResource = $this->blockResourceFactory->create();

        /** @var BlockCollection $blockCollection */
        $blockCollection = $this->blockCollectionFactory->create();
        $blockCollection->load();
        foreach ($blockCollection->getItems() as $block) {
            /** @var \Magento\Cms\Model\Block $block */
            $this->updateData($configuration, $block);
            $blockResource->save($block);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Cms\Model\Block $block
     * @return \Magento\Cms\Model\Block
     */
    protected function updateData(Configuration $configuration, \Magento\Cms\Model\Block $block)
    {
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($block->getContent()))) {
            $block->setContent($configuration->getDummyContentText());
        }

        return $block;
    }
}
