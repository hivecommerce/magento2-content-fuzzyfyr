<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HiveCommerce\ContentFuzzyfyr\Observer;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
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
    public function isValid(Configuration $configuration)
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
            /** @var \Magento\Customer\Model\Customer $customer */
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
            $this->doUpdateQuoteAddress($configuration, $quote->getBillingAddress());
            $this->quoteRepository->save($quote);
        }

        /*
         * Orders
         */
        /** @var OrderAddressCollection $orderAddressCollection */
        $orderAddressCollection = $this->orderAddressRepository->getList($searchCriteria);
        foreach ($orderAddressCollection->getItems() as $orderAddress) {
            $this->doUpdateOrderAddress($configuration, $orderAddress);
            $this->orderAddressRepository->save($orderAddress);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function doUpdateCustomer(
        Configuration $configuration,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        $customer->setEmail(
            sprintf(
                $configuration->getDummyContentEmail(),
                $customer->getId()
            )
        );

        $customer->setLastName($configuration->getDummyContentText());

        $addresses = $customer->getAddresses();
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
     */
    protected function doUpdateQuoteAddress(
        Configuration $configuration,
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress
    ) {
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
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $orderAddress
     */
    protected function doUpdateOrderAddress(
        Configuration $configuration,
        \Magento\Sales\Api\Data\OrderAddressInterface $orderAddress
    ) {
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
