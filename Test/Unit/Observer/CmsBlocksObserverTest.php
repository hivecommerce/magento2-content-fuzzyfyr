<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Observer\CmsBlocksObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\ResourceModel\Block\Collection as BlockCollection;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BlocksObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class BlocksObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $blockResourceFactory = $this->getBlockResourceFactory();
        $blockResourceFactory->expects($this->never())
            ->method('create');

        $blockCollectionFactory = $this->getBlockCollectionFactory();
        $blockCollectionFactory->expects($this->never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToCmsBlocks')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new CmsBlocksObserver($blockCollectionFactory, $blockResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $block = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->getMock();
        $block->expects($this->once())
            ->method('getData')
            ->With('content')
            ->willReturn(null);
        $block->expects($this->once())
            ->method('setData')
            ->with('content', 'dummy-text');

        $blockResource = $this->getMockBuilder(BlockResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockResource->expects($this->once())
            ->method('save')
            ->with($block);
        $blockResourceFactory = $this->getBlockResourceFactory($blockResource);

        $blockCollection = $this->getMockBuilder(BlockCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $blockCollection->expects($this->once())
            ->method('load');
        $blockCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$block]);
        $blockCollectionFactory = $this->getBlockCollectionFactory($blockCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToCmsBlocks')
            ->willReturn(true);
        $configuration->expects($this->any())
            ->method('isUseOnlyEmpty')
            ->willReturn(true);
        $configuration->expects($this->any())
            ->method('getDummyContentText')
            ->willReturn('dummy-text');
        $configuration->expects($this->any())
            ->method('getDummyContentEmail')
            ->willReturn('dummy-email');

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);


        $observer = new CmsBlocksObserver($blockCollectionFactory, $blockResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @return MockObject|Observer
     */
    private function getObserver()
    {
        return $this->createMock(Observer::class);
    }

    /**
     * @return MockObject|Configuration
     */
    private function getConfiguration()
    {
        return $this->createMock(Configuration::class);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Cms\Model\ResourceModel\BlockFactory
     */
    private function getBlockResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Cms\Model\ResourceModel\Block', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private function getBlockCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Cms\Model\ResourceModel\Block\Collection', $instance);
    }
}
