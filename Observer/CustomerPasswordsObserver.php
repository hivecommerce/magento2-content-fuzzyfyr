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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerPasswordsObserver extends FuzzyfyrObserver
{
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * CustomersObserver constructor.
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Configuration $configuration): bool
    {
        return $configuration->isApplyToCustomers();
    }


    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var CustomerCollection $customerCollection */
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->load();
        foreach ($customerCollection->getItems() as $customer) {
            /** @var Customer $customer */
            $customerData = $this->customerRepository->getById($customer->getId());
            $this->customerRepository->save($customerData, $configuration->getDummyPassword());
        }
    }
}
