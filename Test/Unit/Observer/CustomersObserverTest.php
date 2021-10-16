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
use HiveCommerce\ContentFuzzyfyr\Observer\CustomersObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CustomersObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class CustomersObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchCriteriaInterfaceFactory = $this->getSearchCriteriaInterfaceFactory($searchCriteria);

        $orderAddressRepository = $this->getOrderAddressRepository();

        $quoteRepository = $this->getQuoteRepository();

        $customerRepository = $this->getCustomerRepository();
        $customerRepository->expects(self::never())
            ->method('getById');

        $customerCollectionFactory = $this->getCustomerCollectionFactory();
        $customerCollectionFactory->expects(self::never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToCustomers')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new CustomersObserver(
            $customerCollectionFactory,
            $customerRepository,
            $quoteRepository,
            $orderAddressRepository,
            $searchCriteriaInterfaceFactory
        );

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchCriteriaInterfaceFactory = $this->getSearchCriteriaInterfaceFactory($searchCriteria);

        $quoteAddress = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $quoteAddress->expects(self::once())
            ->method('setFirstname')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setMiddlename')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setLastname')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setCompany')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setEmail')
            ->with('dummy-email');
        $quoteAddress->expects(self::once())
            ->method('setCity')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setPostcode')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setStreet')
            ->with('dummy-text');
        $quoteAddress->expects(self::once())
            ->method('setVatId')
            ->with('dummy-text');
        $quote = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->expects(self::once())
            ->method('getBillingAddress')
            ->willReturn($quoteAddress);
        $searchData = $this->createMock(\Magento\Quote\Api\Data\CartSearchResultsInterface::class);
        $searchData->expects(self::once())
            ->method('getItems')
            ->willReturn([$quote]);
        $quoteRepository = $this->getQuoteRepository();
        $quoteRepository->expects(self::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchData);
        $quoteRepository->expects(self::once())
            ->method('save')
            ->with($quote);

        $orderAddress = $this->createMock(\Magento\Sales\Api\Data\OrderAddressInterface::class);
        $orderAddress->expects(self::once())
            ->method('setFirstname')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setMiddlename')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setLastname')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setCompany')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setEmail')
            ->with('dummy-email');
        $orderAddress->expects(self::once())
            ->method('setCity')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setPostcode')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setStreet')
            ->with('dummy-text');
        $orderAddress->expects(self::once())
            ->method('setVatId')
            ->with('dummy-text');
        $searchData = $this->createMock(\Magento\Sales\Api\Data\OrderAddressSearchResultInterface::class);
        $searchData->expects(self::once())
            ->method('getItems')
            ->willReturn([$orderAddress]);
        $orderAddressRepository = $this->getOrderAddressRepository();
        $orderAddressRepository->expects(self::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchData);
        $orderAddressRepository->expects(self::once())
            ->method('save')
            ->with($orderAddress);

        $address = $this->createMock(AddressInterface::class);
        $address->expects(self::once())
            ->method('setStreet')
            ->with(['dummy-text']);
        $address->expects(self::once())
            ->method('setCity')
            ->with('dummy-text');

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects(self::once())
            ->method('getId')
            ->willReturn(42);
        $customerData = $this->createMock(CustomerInterface::class);
        $customerData->expects(self::once())
            ->method('setEmail')
            ->with('dummy-email');
        $customerData->expects(self::once())
            ->method('setLastname')
            ->with('dummy-text');
        $customerData->expects(self::once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customerData->expects(self::once())
            ->method('setAddresses')
            ->with([$address]);

        $customerRepository = $this->getCustomerRepository();
        $customerRepository->expects(self::once())
            ->method('getById')
            ->with(42)
            ->willReturn($customerData);
        $customerRepository->expects(self::once())
            ->method('save')
            ->with($customerData);

        $customerCollection = $this->getMockBuilder(CustomerCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerCollection->expects(self::once())
            ->method('load');
        $customerCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$customer]);
        $customerCollectionFactory = $this->getCustomerCollectionFactory($customerCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToCustomers')
            ->willReturn(true);
        $configuration->expects(self::any())
            ->method('getDummyContentText')
            ->willReturn('dummy-text');
        $configuration->expects(self::any())
            ->method('getDummyContentEmail')
            ->willReturn('dummy-email');

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);


        $observer = new CustomersObserver(
            $customerCollectionFactory,
            $customerRepository,
            $quoteRepository,
            $orderAddressRepository,
            $searchCriteriaInterfaceFactory
        );

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
     * @return MockObject|CustomerRepositoryInterface
     */
    private function getCustomerRepository()
    {
        return $this->createMock(CustomerRepositoryInterface::class);
    }

    /**
     * @return MockObject|QuoteRepository
     */
    private function getQuoteRepository()
    {
        return $this->getMockBuilder(QuoteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject|OrderAddressRepositoryInterface
     */
    private function getOrderAddressRepository()
    {
        return $this->getMockBuilder(OrderAddressRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private function getCustomerCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock(\Magento\Customer\Model\ResourceModel\Customer\Collection::class, $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\Framework\Api\SearchCriteriaInterfaceFactory
     */
    private function getSearchCriteriaInterfaceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock(\Magento\Framework\Api\SearchCriteriaInterface::class, $instance);
    }
}
