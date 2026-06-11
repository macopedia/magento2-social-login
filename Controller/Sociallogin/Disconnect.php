<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Controller\Sociallogin;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

class Disconnect extends \Magento\Customer\Controller\AbstractAccount
{
    private $customerSession;
    private $socialNetworkCustomerRepository;
    private $logger;

    public function __construct(
        Context $context,
        Session $customerSession,
        \Techyouknow\SocialLogin\Model\Repository\SocialLoginCustomerRepository $socialNetworkCustomerRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->socialNetworkCustomerRepository = $socialNetworkCustomerRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $adapterId = $this->getRequest()->getParam('provider');
        $customerId = (int) $this->customerSession->getCustomerId();

        if (!$adapterId) {
            $this->messageManager->addErrorMessage(__('Invalid request.'));
            return $this->resultRedirectFactory->create()->setPath('customer/sociallogin');
        }

        try {
            $connection = $this->socialNetworkCustomerRepository->getByCustomerIdAndType($customerId, $adapterId);

            if (!$connection) {
                $this->messageManager->addErrorMessage(
                    __('No %1 account connected.', ucfirst($adapterId))
                );
                return $this->resultRedirectFactory->create()->setPath('customer/sociallogin');
            }

            $this->socialNetworkCustomerRepository->delete($connection);
            $this->messageManager->addSuccessMessage(
                __('%1 account disconnected successfully.', ucfirst($adapterId))
            );

        } catch (\Exception $e) {
            $this->logger->critical('Social disconnect error: ' . $e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('Could not disconnect %1 account. Please try again.', ucfirst($adapterId)));
        }

        return $this->resultRedirectFactory->create()->setPath('customer/sociallogin');
    }
}
