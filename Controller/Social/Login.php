<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Controller\Social;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

class Login extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    private $socialModel;
    private $customerRepository;
    private $customerModelFactory;
    private $socialNetworkCustomerRepository;
    private $socialHelper;
    private $customerSession;
    private $logger;

    public function __construct(
        Context $context,
        \Techyouknow\SocialLogin\Model\Social $socialModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Techyouknow\SocialLogin\Api\SocialNetworkCustomerRepositoryInterface $socialNetworkCustomerRepository,
        \Techyouknow\SocialLogin\Helper\Social $socialHelper,
        Session $customerSession,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->socialModel = $socialModel;
        $this->customerRepository = $customerRepository;
        $this->customerModelFactory = $customerModelFactory;
        $this->socialNetworkCustomerRepository = $socialNetworkCustomerRepository;
        $this->socialHelper = $socialHelper;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    public function execute()
    {
        $adapterId = $this->getRequest()->getParam('provider');

        if (!$this->checkAdapterIdActive($adapterId)) {
            $this->_redirect('/');
            return;
        }

        try {
            $userProfile = $this->socialModel->getSocialUserProfile($adapterId);

            if ($this->customerSession->isLoggedIn()) {
                // Logged in — connect social account
                $customerId = (int) $this->customerSession->getCustomerId();
                if (!$this->socialNetworkCustomerRepository->socialNetworkCustomerExists($userProfile, $adapterId)) {
                    $this->socialModel->createSocialLoginCustomer($userProfile, $adapterId, $customerId);
                    $this->messageManager->addSuccessMessage(
                        __('%1 account connected successfully.', ucfirst($adapterId))
                    );
                } else {
                    $this->messageManager->addErrorMessage(
                        __('This %1 account is already connected to another customer.', ucfirst($adapterId))
                    );
                }
                return $this->resultRedirectFactory->create()->setPath('customer/sociallogin');
            }

            // Guest — log in or create account
            try {
                $customer = $this->customerRepository->get($userProfile['email']);
                $customerModel = $this->customerModelFactory->create()->load($customer->getId());

                if (!$this->socialNetworkCustomerRepository->socialNetworkCustomerExists($userProfile, $adapterId)) {
                    $this->socialModel->createSocialLoginCustomer($userProfile, $adapterId, $customer->getId());
                }

                $this->socialModel->refresh($customerModel);

                if ($adapterId == 'apple') {
                    $this->_view->loadLayout(['custom_script']);
                    $this->_view->renderLayout();
                    return;
                }

                return $this->resultRedirectFactory->create()->setPath('customer/account');

            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->customerSession->setData(
                    'social_login_pending_profile',
                    array_merge($userProfile, ['adapter_id' => $adapterId])
                );
                return $this->resultRedirectFactory->create()->setPath('customer/social/completeregistration');
            }

        } catch (\Exception $e) {
            $this->logger->critical('Social login error: ' . $e->getMessage(), ['exception' => $e]);

            if ($this->customerSession->isLoggedIn()) {
                // Error during connect — return to connections page with error message
                $this->messageManager->addErrorMessage(
                    __('Could not connect %1 account. Please try again.', ucfirst($adapterId))
                );
                return $this->resultRedirectFactory->create()->setPath('customer/sociallogin');
            }

            // Error during login — redirect to login page with error message
            $this->messageManager->addErrorMessage(
                __('Could not sign in with %1. Please try again or use a different method.', ucfirst((string) $adapterId))
            );
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }
    }

    public function checkAdapterIdActive($adapterId)
    {
        return array_key_exists($adapterId, $this->socialHelper->getActiveSocialNetworksList());
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}