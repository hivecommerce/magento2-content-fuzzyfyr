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
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\ResourceModel\UserFactory as UserResourceFactory;
use Magento\User\Model\User;

class UsersObserver extends FuzzyfyrObserver
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
    public function isValid(Configuration $configuration): bool
    {
        return $configuration->isApplyToUsers();
    }

    /**
     * {@inheritdoc}
     */
    protected function run(Configuration $configuration)
    {
        /** @var UserResource $userResource */
        $userResource = $this->userResourceFactory->create();

        /** @var UserCollection $userCollection */
        $userCollection = $this->userCollectionFactory->create();
        $userCollection->load();
        foreach ($userCollection->getItems() as $user) {
            /** @var User $user */
            $this->doUpdate($configuration, $user);
            $userResource->save($user);
        }
    }

    /**
     * @param Configuration $configuration
     * @param User $user
     * @return void
     */
    protected function doUpdate(Configuration $configuration, User $user): void
    {
        $user->setEmail(
            sprintf(
                $configuration->getDummyContentEmail(),
                $user->getId()
            )
        );

        $user->setLastName($configuration->getDummyContentText());
    }
}
