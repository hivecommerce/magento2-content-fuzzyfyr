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

use HiveCommerce\ContentFuzzyfyr\Api\Observer\FuzzyfyrObserverInterface;
use HiveCommerce\ContentFuzzyfyr\Console\Command\FuzzyfyrCommand;
use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;

abstract class FuzzyfyrObserver implements FuzzyfyrObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationByEvent(Observer $observer): Configuration
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        return $configuration;
    }

    /**
     * @param Configuration $configuration
     * @return bool
     */
    protected function isValid(Configuration $configuration): bool
    {
        return true;
    }


    /**
     * {@inheritdoc}
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $this->getConfigurationByEvent($observer);

        if (!$this->isValid($configuration)) {
            return;
        }

        $this->run($configuration);
    }

    /**
     * @return void
     */
    abstract protected function run(Configuration $configuration);

    /**
     * {@inheritdoc}
     */
    public function updateData(
        DataObject $entity,
        string $fieldName,
        Configuration $configuration,
        $value = FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_TEXT
    ): void {
        $data = (string) $entity->getData($fieldName);
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && ($data === ''))) {
            $entity->setData($fieldName, $value);
        }
    }
}
