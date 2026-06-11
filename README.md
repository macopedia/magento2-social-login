# Techyouknow Social Login for Magento 2

Techyouknow Social Login for Magento 2 is a powerful extension that streamlines the login process for your customers by offering them the convenience of logging in with their existing social media accounts. With support for a wide range of popular platforms including Google, Facebook, Apple, Twitter, Instagram, LinkedIn, Amazon, Yahoo, and GitHub, this module enhances the user experience on your Magento store.

[![Latest Stable Version](http://poser.pugx.org/techyouknow/module-social-login/v)](https://packagist.org/packages/techyouknow/module-social-login)
[![Total Downloads](http://poser.pugx.org/techyouknow/module-social-login/downloads)](https://packagist.org/packages/techyouknow/module-social-login)

---

## Version 2.0.0 — Breaking Changes

> **Version 2.0.0 is NOT backward compatible with version 1.x.**
> If you are upgrading from 1.x, read this section carefully before proceeding.

### Requirements

| | v1.x | v2.0.0 |
|---|---|---|
| PHP | ^7.4 / ^8.x | **>=8.3** |
| Magento | 2.4.x | **2.4.8** |

### What changed in 2.0.0

#### `etc/frontend/routes.xml` — route prefix changed

This is the most impactful breaking change. The custom frontend route was replaced with an extension of the built-in `customer` route.

**Before (v1.x):**
```xml
<router id="standard">
    <route id="techyouknow_redirect" frontName="techyouknow_redirect">
        <module name="Techyouknow_SocialLogin"/>
    </route>
</router>
```

**After (v2.0.0):**
```xml
<router id="standard">
    <route id="customer" frontName="customer">
        <module name="Techyouknow_SocialLogin" before="Magento_Customer"/>
    </route>
</router>
```

**Impact on URLs:**

| Action | v1.x URL | v2.0.0 URL |
|---|---|---|
| Social login callback | `techyouknow_redirect/social/login` | `customer/social/login` |
| Social connect | `techyouknow_redirect/sociallogin/connect` | `customer/sociallogin/connect` |
| Social disconnect | `techyouknow_redirect/sociallogin/disconnect` | `customer/sociallogin/disconnect` |
| Complete registration | `techyouknow_redirect/social/completeregistration` | `customer/social/completeregistration` |

All OAuth redirect URIs configured in your social provider dashboards (Google, Facebook, Apple, etc.) **must be updated** to reflect the new URL prefix.

#### New controllers and features

- **`Controller/Sociallogin/Connect.php`** — dedicated action for connecting a social account to an existing Magento customer account.
- **`Controller/Sociallogin/Disconnect.php`** — dedicated action for unlinking a social provider from an account.
- **`Controller/Social/SaveRegistration.php`** — handles saving of the complete-registration form submitted after first OAuth login.
- **`Block/Account/SocialConnections.php`** + `view/frontend/layout/customer_account.xml` — new block and layout entry that displays connected social accounts on the customer account dashboard.
- **`Block/Social/CompleteRegistration.php`** + `view/frontend/templates/social/complete_registration.phtml` — dedicated block and template for the registration-completion flow shown when a required field (e.g. email) is missing from the OAuth profile.

#### Removed dependency

`firebase/php-jwt` is no longer a Composer dependency. JWT handling is now done through `hybridauth/hybridauth` (~3.0) exclusively.

#### PHP 8.3 compatibility

- Fixed dynamic property deprecation errors (`Block/SocialBlock.php`, `Plugin/SessionConfig.php`, `Model/Social.php`, `Model/SocialLoginCustomer.php`).
- All classes now declare typed properties explicitly.

#### Other changes

- `etc/module.xml` — removed redundant Magento version sequence metadata; module now properly declares `Magento_Customer` in `<sequence>`.
- `etc/adminhtml/system.xml` — configuration structure cleaned up and reduced in size.
- `i18n/pl_PL.csv` — Polish translations extended with new strings.

---

## Features

- **Effortless Login:** Enable your customers to log in seamlessly using their preferred social media accounts, eliminating the need to remember yet another set of credentials.
- **Expanded Reach:** Tap into the vast user bases of various social media platforms, making it easier for a broader audience to engage with your store.
- **Account Connections:** Customers can connect or disconnect social providers from their account dashboard.
- **Complete Registration Flow:** When required profile data is missing from OAuth (e.g. no email from Apple), customers are prompted to complete their profile before the account is created.
- **Secure and Trustworthy:** Leverages the security protocols of well-established social networks to ensure the safety of customer data.

## Supported Social Providers

| Provider | Tested on v2.0.0 |
|---|---|
| Google | ✅ |
| Facebook | ✅ |
| Apple | — |
| Twitter | — |
| Instagram | — |
| LinkedIn | — |
| Amazon | — |
| Yahoo | — |
| GitHub | — |

## Installation

```bash
composer require techyouknow/module-social-login
bin/magento module:enable Techyouknow_SocialLogin
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

After installation, update the redirect URIs in each social provider's developer console to use the new `customer/social/login` URL pattern (see the route changes table above).
