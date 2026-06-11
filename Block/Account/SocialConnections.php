<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Techyouknow\SocialLogin\Helper\Social as SocialHelper;
use Techyouknow\SocialLogin\Model\Repository\SocialLoginCustomerRepository;

class SocialConnections extends Template
{
    private $customerSession;
    private $socialHelper;
    private $socialLoginCustomerRepository;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        SocialHelper $socialHelper,
        SocialLoginCustomerRepository $socialLoginCustomerRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->socialHelper = $socialHelper;
        $this->socialLoginCustomerRepository = $socialLoginCustomerRepository;
    }

    public function getEnabledSocialNetworks(): array
    {
        return $this->socialHelper->getActiveSocialNetworksList();
    }

    public function getConnectedProviders(): array
    {
        $customerId = (int) $this->customerSession->getCustomerId();
        $connections = $this->socialLoginCustomerRepository->getByCustomerId($customerId);

        $connected = [];
        foreach ($connections as $connection) {
            $connected[$connection->getSocialType()] = $connection;
        }

        return $connected;
    }

    public function getCustomerId(): int
    {
        return (int) $this->customerSession->getId();
    }

    public function getConnectUrl(string $provider): string
    {
        return $this->getUrl('customer/sociallogin/connect', ['provider' => $provider]);
    }

    public function getDisconnectUrl(string $provider): string
    {
        return $this->getUrl('customer/sociallogin/disconnect', ['provider' => $provider]);
    }
}
