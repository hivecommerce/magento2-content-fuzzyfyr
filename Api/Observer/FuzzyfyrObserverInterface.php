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

namespace HiveCommerce\ContentFuzzyfyr\Api\Observer;

use HiveCommerce\ContentFuzzyfyr\Console\Command\FuzzyfyrCommand;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

interface FuzzyfyrObserverInterface extends ObserverInterface
{
    /**
     * @param Observer $observer
     * @return Configuration
     */
    public function getConfigurationByEvent(Observer $observer): Configuration;

    /**
     * @param DataObject $entity
     * @param string $fieldName
     * @param Configuration $configuration
     * @param string $value
     * @return void
     */
    public function updateData(
        DataObject $entity,
        string $fieldName,
        Configuration $configuration,
        string $value = FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_TEXT
    ): void;
}
