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

namespace HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer;

use HiveCommerce\ContentFuzzyfyr\Model\Configuration;
use HiveCommerce\ContentFuzzyfyr\Observer\UsersObserver;
use HiveCommerce\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\User\Model\User;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
use Magento\User\Model\ResourceModel\User as UserResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UsersObserverTest
 * @package HiveCommerce\ContentFuzzyfyr\Test\Unit\Observer
 */
class UsersObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully(): void
    {
        $userResourceFactory = $this->getUserResourceFactory();
        $userResourceFactory->expects(self::never())
            ->method('create');

        $userCollectionFactory = $this->getUserCollectionFactory();
        $userCollectionFactory->expects(self::never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToUsers')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects(self::once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new UsersObserver($userCollectionFactory, $userResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects(self::once())
            ->method('setEmail')
            ->with('dummy-email');
        $user->expects(self::once())
            ->method('setLastname')
            ->with('dummy-text');

        $userResource = $this->getMockBuilder(UserResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userResource->expects(self::once())
            ->method('save')
            ->with($user);
        $userResourceFactory = $this->getUserResourceFactory($userResource);

        $userCollection = $this->getMockBuilder(UserCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userCollection->expects(self::once())
            ->method('load');
        $userCollection->expects(self::once())
            ->method('getItems')
            ->willReturn([$user]);
        $userCollectionFactory = $this->getUserCollectionFactory($userCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects(self::once())
            ->method('isApplyToUsers')
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


        $observer = new UsersObserver($userCollectionFactory, $userResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @return MockObject&Observer
     */
    private function getObserver()
    {
        return $this->createMock(Observer::class);
    }

    /**
     * @return MockObject&Configuration
     */
    private function getConfiguration()
    {
        return $this->createMock(Configuration::class);
    }

    /**
     * @param MockObject $instance
     * @return MockObject&\Magento\User\Model\ResourceModel\UserFactory
     */
    private function getUserResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\User\Model\ResourceModel\User', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject&\Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private function getUserCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\User\Model\ResourceModel\User\Collection', $instance);
    }
}
