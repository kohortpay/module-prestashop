# Official KohortPay Prestashop module

This module allows you to lower your acquisition costs by turning your customers into your brands advocates. They will have the opportunity to invite all their friends to buy more on your store in exchange for cashback.
It includes two isolated features : 
- Either you add a new payment method offering a referral program directly from the payment page : `KohortPay`
- Or you add a referral program whithin the discount code field on the cart page : `KohortRef`


## Table of content

- [Official KohortPay Prestashop module](#official-kohortpay-prestashop-module)
  - [Table of content](#table-of-content)
  - [What is KohortPay](#what-is-kohortpay)
    - [Description](#description)
    - [Benefits](#benefits)
  - [Module overview](#module-overview)
    - [Version](#version)
    - [Licence](#licence)
    - [Compabilities and Restrictions](#compabilities-and-restrictions)
    - [Features KohortPay](#features-kohortpay)
    - [Features KohortRef](#features-kohortref)
  - [Installation](#installation)
    - [Using ZIP](#using-zip)
    - [Using Prestashop marketplace](#using-prestashop-marketplace)
    - [Using Symfony CLI](#using-symfony-cli)
    - [Using Composer](#using-composer)
  - [Configuration KohortPay](#configuration-kohortpay)
    - [Prerequisites](#prerequisites)
    - [Activation](#activation)
    - [API Secret Key](#api-secret-key)
    - [Minimum amount](#minimum-amount)
  - [Configuration KohortRef](#configuration-kohortref)
    - [Prerequisites](#prerequisites-1)
    - [Activation](#activation-1)
    - [API Secret Key](#api-secret-key-1)
    - [WEBHOOK Secret Key](#webhook-secret-key)
    - [Payment Methods](#payment-methods)
    - [Minimum amount](#minimum-amount-1)
  - [Demo](#demo)
  - [Testing](#testing)
    - [Requirements](#requirements)
    - [Starting Prestashop](#starting-prestashop)
    - [Testing the module](#testing-the-module)
    - [Stopping Prestashop](#stopping-prestashop)
  - [Need help](#need-help)
    - [Documentation](#documentation)
    - [Feedback](#feedback)
    - [Support](#support)

## What is KohortPay

### Description

KohortPay lets your customers pay, refer and save on every purchase. Cut your customer acquisition costs in half while offering your customers a social and fun brand experience. And just like that, your checkout becomes so koool.

### Benefits

- **No setup costs**: integrate KohortPay on your site and increase customer satisfaction in 10 minutes - ready, set, GO.
- **Lower your acquisition costs**: drive high-quality customer acquisition at half the cost of existing overpriced customer acquisition channels.
- **Pay for performance**: no commitments. You only pay for results. Start and stop in 1 click.
- **Brand reinforcement**: generate content from customers and harness the power of  word-of-mouth recommendations. Personalize KohortPay to look and feel like your brand. Configure the experience to overcome your challenges and meet your objectives.

## Module overview

### Version
Current version is 1.1.0. See all [releases here](https://github.com/kohortpay/module-prestashop/releases).

### Licence
The module and this repository is under MIT License. 

### Compabilities and Restrictions
- Only FR and EN languages available.
- Only EUR currency available.
- Works and has been tested with Prestashop 1.7 and 8 (should work with Prestashop 1.6 but not tested, use at your own risk).
- You should use only 2 decimals for your price and round them on each item.

### Features KohortPay
- Add a new payment method that you customer will love (Pay less, together)
- Redirect to an awesome and customized payment page (using you customer cart details).
- Enable/disable the module by a simple switch through the settings.
- Possibility to set minimun amount, under which the payment is disabled. 
- Easy way to switch live/test mode by filling you API secret key (sk or sk_test).
- Handle API errors (with more details if Prestashop is in debug mode).

### Features KohortRef
- Add a new referral program that you customer will love (Pay less, together).
- All orders will create a new referral group (if module is enabled for the specific payment method).
- Customers can join a group by adding the referral code inside discount code field on the cart page.
- Enable/disable the module by a simple switch through the settings.
- Possibility to set minimun amount, under which the group is not created.
- Easy way to switch live/test mode by filling you API secret key (sk or sk_test).
- Display API errors in Prestashop Log system.

## Installation

### Using ZIP

1. Log into your Prestashop admin
2. Go to Modules > Module Manager
3. Click on the button top-right "`Upload a module`" and select the ZIP from this repository `./kohortpay.zip` 
4. After installation is done, you can configure it ([see instructions below](#configuration))

### Using Prestashop marketplace

Coming soon...

### Using Symfony CLI

Coming soon...

### Using Composer

Coming soon...

## Configuration KohortPay

### Prerequisites
- You should have a KohortPay account. If it's not the case, you can [register here](https://dashboard.kohortpay.com/sign-up).
- You should have installed the module on your Prestashop instance and have access to its settings page.

### Activation

You can display or hide the KohortPay payment method from you checkout page using this configuration (enabled/disabled).
*Nota Bene* : KohortPay and KohortRef could not be enabled at the same time.

### API Secret Key

Found in KohortPay Dashboard > Developer settings > API Keys.
Start with sk_ or sk_test (for test mode).

### Minimum amount

You can define here the total order minimum amount to display the KohortPay payment method (minimum 30€).

## Configuration KohortRef

### Prerequisites
- You should have a KohortRef account. If it's not the case, you can [register here](https://dashboard.kohortpay.com/sign-up).
- You should have installed the module on your Prestashop instance and have access to its settings page.

### Activation

Must be enabled to let your customers refer with KohortRef.
*Nota Bene* : KohortPay and KohortRef could not be enabled at the same time.

### API Secret Key

Found in KohortPay Dashboard > Developer settings > API Keys.
Start with sk_ or sk_test (for test mode).

### WEBHOOK Secret Key

Found in KohortPay Dashboard > Developer settings > Webhooks > Secret Key.
Start with whsec_.

### Payment Methods

Select which payment method is able to create a referral group.
*Nota Bene* : Please select payment method who offer refund mechanism from Prestashop back-office.

### Minimum amount

You can define here the total order minimum amount to create or join a referral group (minimum 30€).

## Demo

You can access a live demo of the KohortPay module here (Prestashop 8.1.0):
- Front-Office : [https://prestashop-demo.kohortpay.com](https://prestashop-demo.kohortpay.com)
- Back-Office : [https://prestashop-demo.kohortpay.com/admin473dhjfcivaqhjeooaz](https://prestashop-demo.kohortpay.com/admin473dhjfcivaqhjeooaz)
    - Login : demo@kohortpay.com
    - Password : demops123

## Testing

If you want to test the KohortPay module on a fresh Prestashop installation, please read the following instruction.
The stack is based on Bitnami Docker image.

### Requirements

- Git ([Install instruction](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git))
- Docker desktop:
  - [Mac install instruction](https://docs.docker.com/desktop/install/mac-install/)
  - [Windows install instruction](https://docs.docker.com/desktop/install/windows-install/)
- Docker Compose: Already included in Docker Desktop.

### Starting Prestashop

1. Start your Docker desktop application
2. Clone the repository and go inside the directory:
   ```
   git clone git@github.com:kohortpay/module-prestashop.git
   cd module-prestashop
   ```
3. Select the prestashop version (8 or 1.7) you want to use within the file `./Dockerfile`
4. Start the docker stack:
   ```
   docker-compose up -d --build
   ```
5. Wait for container to be up (~1 minute), then visit http://localhost/
6. Your prestashop is UP. Enjoy!

### Testing the module

1.  Go to Prestashop admin: http://localhost/administration/
2.  Log in with these credentials:
    - Login: user@example.com
    - Password: bitnami1
3. Install the module ([see instruction above](#installation))
4. Configure the module ([see instructions above](#configuration))
6. Go back to the frontend (http://localhost/) and proceed to the checkout with enough products in your cart (to reach minimum amount defined in settings).
7. At the Step 4, select KohortPay as a payment method and place the order. You should be redirected to KohortPay payment page. Enjoy!

### Stopping Prestashop

When your tests are over, you can stop and destroy everything with the following command:

```
docker-compose down --volumes
```

## Need help

### Documentation
If you have any questions, do not hesitate to check our documentations : 
- [Product Docs](https://docs.kohortpay.com/)
- [API & SDK Reference](https://api-docs.kohortpay.com/)
- [Help Center](https://help.kohortpay.com/fr)

### Feedback
If you have any idea or suggestion to improve our solution or the module, you can send an email to feedback@kohortpay.com. You can also check our [roadmap here](https://roadmap.kohortpay.com/tabs/1-under-consideration).

### Support
If you need help, please contact our support team by sending an email to support@kohortpay.com.

**NB**: We don't provide any SLA on our support response time (best effort). 
