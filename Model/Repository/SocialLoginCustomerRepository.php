<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Model\Repository;

class SocialLoginCustomerRepository implements \Techyouknow\SocialLogin\Api\SocialNetworkCustomerRepositoryInterface
{
    private $collectionFactory;
    private $socialLoginCustomerFactory;

    public function __construct(
        \Techyouknow\SocialLogin\Model\ResourceModel\SocialLoginCustomer\CollectionFactory $collectionFactory,
        \Techyouknow\SocialLogin\Model\SocialLoginCustomerFactory $socialLoginCustomerFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->socialLoginCustomerFactory = $socialLoginCustomerFactory;
    }

    public function getById($id)
    {
        $socialLoginCustomer = $this->socialLoginCustomerFactory->create();
        $socialLoginCustomer->getResource()->load($socialLoginCustomer, $id);

        if (!$socialLoginCustomer->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Unable to find Social Login Customer with ID %1', $id)
            );
        }

        return $socialLoginCustomer;
    }

    public function getByCustomerIdAndType(int $customerId, string $type)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('social_type', $type);

        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : null;
    }

    public function getByCustomerId(int $customerId): array
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);

        return $collection->getItems();
    }

    public function socialNetworkCustomerExists(array $userProfile, string $type): int
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('social_id', $userProfile['identifier'])
            ->addFieldToFilter('social_type', $type)
            ->count();
    }

    public function save(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer $socialNetworkCustomer)
    {
        $socialNetworkCustomer->getResource()->save($socialNetworkCustomer);
        return $socialNetworkCustomer;
    }

    public function delete(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer $socialNetworkCustomer)
    {
        $socialNetworkCustomer->getResource()->delete($socialNetworkCustomer);
        return $socialNetworkCustomer;
    }
}