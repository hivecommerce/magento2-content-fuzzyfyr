<?php
/**
 * This file is part of the Content Fuzzyfyr module for Magento2.
 *
 * (c) All.In Data GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllInData\ContentFuzzyfyr\Test\Unit\Observer;

use AllInData\ContentFuzzyfyr\Model\Configuration;
use AllInData\ContentFuzzyfyr\Observer\UsersObserver;
use AllInData\ContentFuzzyfyr\Test\Unit\AbstractTest;
use Magento\Framework\Event\Observer;
use Magento\User\Model\User;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
use Magento\User\Model\ResourceModel\User as UserResource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UsersObserverTest
 * @package AllInData\ContentFuzzyfyr\Test\Unit\Observer
 */
class UsersObserverTest extends AbstractTest
{
    /**
     * @test
     */
    public function stopOnFailedValidationSuccessfully()
    {
        $userResourceFactory = $this->getUserResourceFactory();
        $userResourceFactory->expects($this->never())
            ->method('create');

        $userCollectionFactory = $this->getUserCollectionFactory();
        $userCollectionFactory->expects($this->never())
            ->method('create');

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToUsers')
            ->willReturn(false);

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);

        $observer = new UsersObserver($userCollectionFactory, $userResourceFactory);

        $observer->execute($eventObserver);
    }

    /**
     * @test
     */
    public function runSuccessfully()
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('setEmail')
            ->with('dummy-email');
        $user->expects($this->once())
            ->method('setLastname')
            ->with('dummy-text');

        $userResource = $this->getMockBuilder(UserResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userResource->expects($this->once())
            ->method('save')
            ->with($user);
        $userResourceFactory = $this->getUserResourceFactory($userResource);

        $userCollection = $this->getMockBuilder(UserCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userCollection->expects($this->once())
            ->method('load');
        $userCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$user]);
        $userCollectionFactory = $this->getUserCollectionFactory($userCollection);

        $configuration = $this->getConfiguration();
        $configuration->expects($this->once())
            ->method('isApplyToUsers')
            ->willReturn(true);
        $configuration->expects($this->any())
            ->method('getDummyContentText')
            ->willReturn('dummy-text');
        $configuration->expects($this->any())
            ->method('getDummyContentEmail')
            ->willReturn('dummy-email');

        $eventObserver = $this->getObserver();
        $eventObserver->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->willReturn($configuration);


        $observer = new UsersObserver($userCollectionFactory, $userResourceFactory);

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
     * @param MockObject $instance
     * @return MockObject|\Magento\User\Model\ResourceModel\UserFactory
     */
    private function getUserResourceFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\User\Model\ResourceModel\User', $instance);
    }

    /**
     * @param MockObject $instance
     * @return MockObject|\Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private function getUserCollectionFactory(MockObject $instance = null)
    {
        return $this->getFactoryAsMock('\Magento\User\Model\ResourceModel\User\Collection', $instance);
    }
}