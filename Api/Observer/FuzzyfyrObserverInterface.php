<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Api\Observer;

use AllInData\ContentFuzzyfyr\Console\Command\FuzzyfyrCommand;
use AllInData\ContentFuzzyfyr\Model\Configuration;
use Magento\Framework\Event\ObserverInterface;

interface FuzzyfyrObserverInterface extends ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return Configuration
     */
    public function getConfigurationByEvent(\Magento\Framework\Event\Observer $observer);

    /**
     * @param \Magento\Framework\DataObject $entity
     * @param string $fieldName
     * @param Configuration $configuration
     * @param string $value
     */
    public function updateData(
        \Magento\Framework\DataObject $entity,
        $fieldName,
        Configuration $configuration,
        $value = FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_TEXT
    );
}
