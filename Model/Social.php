<?php
/*
 * MIT License
 *
 * Copyright (c) 2023 Techyouknow
 */

namespace Techyouknow\SocialLogin\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class Social extends \Magento\Framework\Model\AbstractModel
{
    private $socialHelper;
    private $customerFactory;
    private $customerRepository;
    private $storeManager;
    private $cookieMetadataFactory;
    private $cookieMetadataManager;
    private $session;
    private $customerModelFactory;
    private $accountManagement;
    private $random;
    private $socialLoginCustomerRepository;
    private $socialNetworkCustomer;
    private $dateTime;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Techyouknow\SocialLogin\Helper\Social $socialHelper,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\AccountManagement $accountManagement,
        \Magento\Framework\Math\Random $random,
        \Techyouknow\SocialLogin\Api\Data\SocialNetworkCustomerFactory $socialNetworkCustomer,
        \Techyouknow\SocialLogin\Model\Repository\SocialLoginCustomerRepository $socialLoginCustomerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->socialHelper = $socialHelper;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->session = $session;
        $this->customerModelFactory = $customerModelFactory;
        $this->accountManagement = $accountManagement;
        $this->random = $random;
        $this->socialLoginCustomerRepository = $socialLoginCustomerRepository;
        $this->socialNetworkCustomer = $socialNetworkCustomer;
        $this->dateTime = $dateTime;
    }

    public function getSocialUserProfile($adapterId)
    {
        $adapterName = $this->socialHelper->getSocialNetwork($adapterId);
        $adaptersConfig = $this->socialHelper->getHybridauthConfig($adapterId);

        $hybridauth = new \Hybridauth\Hybridauth($adaptersConfig);
        $adapter = $hybridauth->authenticate($adapterName);
        $userProfile = $adapter->getUserProfile();
        $adapter->disconnect();

        return $this->prepareUserProfile($userProfile, $adapterId);
    }

    public function prepareUserProfile($userProfile, $type)
    {
        $name = explode(' ', $userProfile->displayName ?: __('New User'));

        return [
            'email'      => $userProfile->email ?: $userProfile->identifier . '@' . strtolower($type) . '.com',
            'firstname'  => $userProfile->firstName ?: (array_shift($name) ?: $userProfile->identifier),
            'lastname'   => $userProfile->lastName ?: (array_shift($name) ?: $userProfile->identifier),
            'identifier' => $userProfile->identifier,
            'type'       => $type,
            'password'   => $userProfile->password ?? null,
        ];
    }

    public function createCustomerAccount($userProfile, $type)
    {
        $store = $this->storeManager->getStore();

        $customer = $this->customerFactory->create();
        $customer
            ->setFirstname($userProfile['firstname'])
            ->setLastname($userProfile['lastname'])
            ->setEmail($userProfile['email'])
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setCreatedIn($store->getName());

        $customer = $this->customerRepository->save($customer);
        $this->createSocialLoginCustomer($userProfile, $type, $customer->getId());

        $newPasswordToken = $this->random->getUniqueHash();
        $this->accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);

        return $this->customerModelFactory->create()->load($customer->getId());
    }

    public function createSocialLoginCustomer($userProfile, $type, $customerId)
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $socialNetworkCustomer = $this->socialNetworkCustomer->create();
        $socialNetworkCustomer
            ->setSocialId($userProfile['identifier'])
            ->setCustomerId($customerId)
            ->setSocialType($type)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->socialLoginCustomerRepository->save($socialNetworkCustomer);
    }

    public function refresh($customer)
    {
        if ($customer && $customer->getId()) {
            $this->session->setCustomerAsLoggedIn($customer);
            $this->session->regenerateId();

            if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }
}