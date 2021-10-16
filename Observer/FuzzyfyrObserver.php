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

abstract class FuzzyfyrObserver implements FuzzyfyrObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationByEvent(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        return $configuration;
    }

    /**
     * @param Configuration $configuration
     * @return mixed
     */
    protected function isValid(Configuration $configuration)
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
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
        \Magento\Framework\DataObject $entity,
        $fieldName,
        Configuration $configuration,
        $value = FuzzyfyrCommand::DEFAULT_DUMMY_CONTENT_TEXT
    ) {
        if (!$configuration->isUseOnlyEmpty() ||
            ($configuration->isUseOnlyEmpty() && empty($entity->getData($fieldName)))) {
            $entity->setData($fieldName, $value);
        }
    }
}
