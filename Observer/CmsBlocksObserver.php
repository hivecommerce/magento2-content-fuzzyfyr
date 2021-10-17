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

use Exception;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use Magento\Cms\Model\Block;
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
     *
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
    public function isValid(Configuration $configuration): bool
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
            /** @var Block $block */
            $this->doUpdate($configuration, $block);
            $blockResource->save($block);
        }
    }

    /**
     * @param Configuration $configuration
     * @param Block $block
     * @return void
     */
    protected function doUpdate(Configuration $configuration, Block $block): void
    {
        $this->updateData($block, 'content', $configuration, $configuration->getDummyContentText());
    }
}
