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
    public function isValid(Configuration $configuration)
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
            /** @var \Magento\Customer\Model\Customer $customer */
            $customerData = $this->customerRepository->getById($customer->getId());
            $this->doUpdate($configuration, $customerData);
            $this->customerRepository->save($customerData);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function doUpdate(Configuration $configuration, \Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customer->setPassword($configuration->getDummyPassword());
    }
}