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
use HiveCommerce\ContentFuzzyfyr\Observer\CustomerPasswordsObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CustomerPasswordsObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class CustomerPasswordsObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
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

        $observer = new CustomerPasswordsObserver($customerCollectionFactory, $customerRepository);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects(self::once())
            ->method('getId')
            ->willReturn(42);
        $customerData = $this->getMockBuilder(CustomerInterface::class)
            ->setMethods([
                'setPassword',
                'getId',
                'setId',
                'getGroupId',
                'setGroupId',
                'getDefaultBilling',
                'setDefaultBilling',
                'getDefaultShipping',
                'setDefaultShipping',
                'getConfirmation',
                'setConfirmation',
                'getCreatedAt',
                'setCreatedAt',
                'getUpdatedAt',
                'setUpdatedAt',
                'getCreatedIn',
                'setCreatedIn',
                'getDob',
                'setDob',
                'getEmail',
                'setEmail',
                'getFirstname',
                'setFirstname',
                'getLastname',
                'setLastname',
                'getMiddlename',
                'setMiddlename',
                'getPrefix',
                'setPrefix',
                'getSuffix',
                'setSuffix',
                'getGender',
                'setGender',
                'getStoreId',
                'setStoreId',
                'getTaxvat',
                'setTaxvat',
                'getWebsiteId',
                'setWebsiteId',
                'getAddresses',
                'setAddresses',
                'getDisableAutoGroupChange',
                'setDisableAutoGroupChange',
                'getExtensionAttributes',
                'setExtensionAttributes',
                'getCustomAttribute',
                'setCustomAttribute',
                'getCustomAttributes',
                'setCustomAttributes'
            ])
            ->getMock();
        $customerData->expects(self::once())
            ->method('setPassword')
            ->with('password');

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
            ->method('getDummyPassword')
            ->willReturn('password');

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);


        $observer = new CustomerPasswordsObserver($customerCollectionFactory, $customerRepository);

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
     * @param MockObject $instance
     * @return MockObject|\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private function getCustomerCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\Customer\Model\ResourceModel\Customer\Collection', $instance);
    }
}
