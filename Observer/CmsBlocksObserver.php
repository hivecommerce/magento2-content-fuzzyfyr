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

class CmsBlocksObserver extends FuzzyfyrObserver
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
    public function isValid(Configuration $configuration)
    {
        return $configuration->isApplyToCmsBlocks();
    }

    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var BlockResource $blockResource */
        $blockResource = $this->blockResourceFactory->create();

        /** @var BlockCollection $blockCollection */
        $blockCollection = $this->blockCollectionFactory->create();
        $blockCollection->load();
        foreach ($blockCollection->getItems() as $block) {
            /** @var \Magento\Cms\Model\Block $block */
            $this->doUpdate($configuration, $block);
            $blockResource->save($block);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Cms\Model\Block $block
     */
    protected function doUpdate(Configuration $configuration, \Magento\Cms\Model\Block $block)
    {
        $this->updateData($block, 'content', $configuration, $configuration->getDummyContentText());
    }
}
