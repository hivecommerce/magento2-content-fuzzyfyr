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
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection as OrderAddressCollection;
use Magento\Quote\Api\QuoteAddressRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Api\SearchCriteriaInterfaceFactory;

class CustomersObserver extends FuzzyfyrObserver
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
     * @var QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var OrderAddressRepositoryInterface
     */
    protected $orderAddressRepository;
    /**
     * @var SearchCriteriaInterfaceFactory
     */
    protected $searchCriteriaFactory;

    /**
     * CustomersObserver constructor.
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param QuoteRepository $quoteRepository
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        QuoteRepository $quoteRepository,
        OrderAddressRepositoryInterface $orderAddressRepository,
        SearchCriteriaInterfaceFactory $searchCriteriaFactory
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->quoteRepository = $quoteRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
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
        /*
         * Customer
         */
        /** @var CustomerCollection $customerCollection */
        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->load();
        foreach ($customerCollection->getItems() as $customer) {
            /** @var Customer $customer */
            $customerData = $this->customerRepository->getById($customer->getId());
            $this->doUpdateCustomer($configuration, $customerData);
            $this->customerRepository->save($customerData);
        }

        $searchCriteria = $this->searchCriteriaFactory->create();
        /*
         * Quotes
         */
        $quoteCollection = $this->quoteRepository->getList($searchCriteria);
        foreach ($quoteCollection->getItems() as $quote) {
            $billingAddress = $quote->getBillingAddress();
            if($billingAddress !== null) {
                $this->doUpdateQuoteAddress($configuration, $billingAddress);
                $this->quoteRepository->save($quote);
            }
        }

        /*
         * Orders
         */
        /** @var OrderAddressCollection $orderAddressCollection */
        $orderAddressCollection = $this->orderAddressRepository->getList($searchCriteria);
        foreach ($orderAddressCollection->getItems() as $orderAddress) {
            /** @var OrderAddressInterface $orderAddress */
            $this->doUpdateOrderAddress($configuration, $orderAddress);
            $this->orderAddressRepository->save($orderAddress);
        }
    }

    /**
     * @param Configuration $configuration
     * @param CustomerInterface $customer
     * @return void
     */
    protected function doUpdateCustomer(
        Configuration $configuration,
        CustomerInterface $customer
    ): void {
        $customer->setEmail(
            sprintf(
                $configuration->getDummyContentEmail(),
                $customer->getId()
            )
        );

        $customer->setLastname($configuration->getDummyContentText());

        $addresses = $customer->getAddresses();
        if($addresses === null) {
            $addresses = [];
        }

        foreach ($addresses as $address) {
            /** @var AddressInterface $address */
            $address->setStreet([$configuration->getDummyContentText()]);
            $address->setCity($configuration->getDummyContentText());
        }
        $customer->setAddresses($addresses);
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Quote\Api\Data\AddressInterface $quoteAddress
     * @return void
     */
    protected function doUpdateQuoteAddress(
        Configuration $configuration,
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress
    ): void {
        $quoteAddress->setFirstname($configuration->getDummyContentText());
        $quoteAddress->setMiddlename($configuration->getDummyContentText());
        $quoteAddress->setLastname($configuration->getDummyContentText());
        $quoteAddress->setCompany($configuration->getDummyContentText());
        $quoteAddress->setEmail($configuration->getDummyContentEmail());
        $quoteAddress->setCity($configuration->getDummyContentText());
        $quoteAddress->setPostcode($configuration->getDummyContentText());
        $quoteAddress->setStreet($configuration->getDummyContentText());
        $quoteAddress->setVatId($configuration->getDummyContentText());
    }

    /**
     * @param Configuration $configuration
     * @param OrderAddressInterface $orderAddress
     * @return void
     */
    protected function doUpdateOrderAddress(
        Configuration $configuration,
        OrderAddressInterface $orderAddress
    ): void {
        $orderAddress->setFirstname($configuration->getDummyContentText());
        $orderAddress->setMiddlename($configuration->getDummyContentText());
        $orderAddress->setLastname($configuration->getDummyContentText());
        $orderAddress->setCompany($configuration->getDummyContentText());
        $orderAddress->setEmail($configuration->getDummyContentEmail());
        $orderAddress->setCity($configuration->getDummyContentText());
        $orderAddress->setPostcode($configuration->getDummyContentText());
        $orderAddress->setStreet($configuration->getDummyContentText());
        $orderAddress->setVatId($configuration->getDummyContentText());
    }
}
