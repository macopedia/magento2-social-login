<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Controller\Sociallogin;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    private $customerSession;
    private $resultPageFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        return $this->resultPageFactory->create();
    }
}
