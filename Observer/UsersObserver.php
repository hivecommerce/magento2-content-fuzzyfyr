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
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\ResourceModel\UserFactory as UserResourceFactory;
use Magento\Framework\Event\ObserverInterface;

class UsersObserver implements ObserverInterface
{
    /**
     * @var UserCollectionFactory
     */
    protected $userCollectionFactory;
    /**
     * @var UserResourceFactory
     */
    protected $userResourceFactory;

    /**
     * UsersObserver constructor.
     * @param UserCollectionFactory $userCollectionFactory
     * @param UserResourceFactory $userResourceFactory
     */
    public function __construct(UserCollectionFactory $userCollectionFactory, UserResourceFactory $userResourceFactory)
    {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->userResourceFactory = $userResourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Configuration $configuration */
        $configuration = $observer->getData('configuration');

        if (!$configuration->isApplyToUsers()) {
            return;
        }

        /** @var UserResource $userResource */
        $userResource = $this->userResourceFactory->create();

        /** @var UserCollection $userCollection */
        $userCollection = $this->userCollectionFactory->create();
        $userCollection->load();
        foreach ($userCollection->getItems() as $user) {
            /** @var \Magento\User\Model\User $user */
            $this->updateData($configuration, $user);
            $userResource->save($user);
        }
    }

    /**
     * @param Configuration $configuration
     * @param \Magento\User\Model\User $user
     * @return \Magento\User\Model\User
     */
    protected function updateData(Configuration $configuration, \Magento\User\Model\User $user)
    {
        $user->setEmail(
            sprintf(
                $configuration->getDummyContentEmail(),
                $user->getId()
            )
        );

        $user->setLastName($configuration->getDummyContentText());

        return $user;
    }
}
