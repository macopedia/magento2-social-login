<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Controller\Social;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

class SaveRegistration extends \Magento\Framework\App\Action\Action
{
    private $customerSession;
    private $formKeyValidator;
    private $accountManagement;
    private $customerRepository;
    private $customerFactory;
    private $customerModelFactory;
    private $storeManager;
    private $socialModel;
    private $logger;

    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Techyouknow\SocialLogin\Model\Social $socialModel,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerModelFactory = $customerModelFactory;
        $this->storeManager = $storeManager;
        $this->socialModel = $socialModel;
        $this->logger = $logger;
    }

    public function execute()
    {
        $redirectBack = $this->resultRedirectFactory->create()
            ->setPath('customer/social/completeregistration');

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please reload the page and try again.'));
            return $redirectBack;
        }

        $profile = $this->customerSession->getData('social_login_pending_profile');
        if (!$profile || empty($profile['email'])) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $firstname = trim((string) $this->getRequest()->getParam('firstname', $profile['firstname'] ?? ''));
        $lastname  = trim((string) $this->getRequest()->getParam('lastname', $profile['lastname'] ?? ''));
        $password  = (string) $this->getRequest()->getParam('password', '');
        $passwordConfirm = (string) $this->getRequest()->getParam('password_confirmation', '');

        $errors = $this->validateName($firstname, $lastname, $password, $passwordConfirm);
        if ($errors) {
            foreach ($errors as $error) {
                $this->messageManager->addErrorMessage($error);
            }
            return $redirectBack;
        }

        try {
            try {
                $this->customerRepository->get($profile['email']);
                $this->customerSession->unsetData('social_login_pending_profile');
                $this->messageManager->addErrorMessage(
                    __('An account with this email already exists. Please log in.')
                );
                return $this->resultRedirectFactory->create()->setPath('customer/account/login');
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // email is free, proceed with account creation
            }

            $store = $this->storeManager->getStore();
            $customer = $this->customerFactory->create();
            $customer
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setEmail($profile['email'])
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setCreatedIn($store->getName());

            $savedCustomer = $this->accountManagement->createAccount($customer, $password);

            $this->socialModel->createSocialLoginCustomer(
                $profile,
                $profile['adapter_id'],
                $savedCustomer->getId()
            );

            $this->customerSession->unsetData('social_login_pending_profile');

            $customerModel = $this->customerModelFactory->create()->load($savedCustomer->getId());
            $this->socialModel->refresh($customerModel);

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $redirectBack;
        } catch (\Exception $e) {
            $this->logger->critical('Social login complete registration error: ' . $e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('An error occurred while creating your account. Please try again.'));
            return $redirectBack;
        }

        return $this->resultRedirectFactory->create()->setPath('customer/account');
    }

    private function validateName(string $firstname, string $lastname, string $password, string $passwordConfirm): array
    {
        $errors = [];

        if (empty($firstname)) {
            $errors[] = __('First name is required.');
        }
        if (empty($lastname)) {
            $errors[] = __('Last name is required.');
        }
        if (strlen($password) < 8) {
            $errors[] = __('Password must be at least 8 characters long.');
        }
        if ($password !== $passwordConfirm) {
            $errors[] = __('Password and password confirmation do not match.');
        }

        return $errors;
    }
}