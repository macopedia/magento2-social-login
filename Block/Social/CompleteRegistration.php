<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Block\Social;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class CompleteRegistration extends Template
{
    private $customerSession;

    public function __construct(
        Template\Context $context,
        Session\Proxy $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    public function getPendingProfile(): array
    {
        return $this->customerSession->getData('social_login_pending_profile') ?? [];
    }

    public function getFormAction(): string
    {
        return $this->getUrl('customer/social/saveregistration');
    }
}