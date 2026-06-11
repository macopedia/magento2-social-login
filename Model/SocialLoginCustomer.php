<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Model;

class SocialLoginCustomer extends \Magento\Framework\Model\AbstractModel implements \Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer
{
    protected function _construct()
    {
        $this->_init(\Techyouknow\SocialLogin\Model\ResourceModel\SocialLoginCustomer::class);
    }

    public function getEntityId()
    {
        return $this->getData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        $this->setData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::ENTITY_ID, $entityId);
        return $this;
    }

    public function getSocialId()
    {
        return $this->getData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::SOCIAL_ID);
    }

    public function setSocialId($socialId)
    {
        $this->setData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::SOCIAL_ID, $socialId);
        return $this;
    }

    public function getCustomerId()
    {
        return $this->getData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        $this->setData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::CUSTOMER_ID, $customerId);
        return $this;
    }

    public function getSocialType()
    {
        return $this->getData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::SOCIAL_TYPE);
    }

    public function setSocialType($socialType)
    {
        $this->setData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::SOCIAL_TYPE, $socialType);
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->getData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::CREATED_AT, $createdAt);
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->getData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->setData(\Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomer::UPDATED_AT, $updatedAt);
        return $this;
    }
}
