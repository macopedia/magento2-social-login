<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Controller\Sociallogin;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Techyouknow\SocialLogin\Helper\Social as SocialHelper;

class Connect extends \Magento\Customer\Controller\AbstractAccount
{
    private $customerSession;
    private $socialHelper;

    public function __construct(
        Context $context,
        Session $customerSession,
        SocialHelper $socialHelper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->socialHelper = $socialHelper;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $adapterId = $this->getRequest()->getParam('provider');
        if (!$adapterId || !array_key_exists($adapterId, $this->socialHelper->getActiveSocialNetworksList())) {
            $this->messageManager->addErrorMessage(__('Invalid social network provider.'));
            return $this->resultRedirectFactory->create()->setPath('customer/sociallogin');
        }

        return $this->resultRedirectFactory->create()->setPath(
            'customer/social/login',
            ['provider' => $adapterId]
        );
    }
}
