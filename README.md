# Official KohortPay Prestashop module

This module adds a new payment method to PrestaShop: `KohortPay`, which allows you turn your customers into your brands advocates by letting them invite their friends with a cashback.


## Table of content

- [Official KohortPay Prestashop module](#official-kohortpay-prestashop-module)
  - [Table of content](#table-of-content)
  - [Overview](#overview)
    - [Description](#description)
    - [Version](#version)
    - [Licence](#licence)
    - [Compabilities and Restrictions](#compabilities-and-restrictions)
    - [Features](#features)
    - [Benefits](#benefits)
  - [Installation](#installation)
    - [Using ZIP](#using-zip)
    - [Using Prestashop marketplace](#using-prestashop-marketplace)
    - [Using Symfony CLI](#using-symfony-cli)
    - [Using Composer](#using-composer)
  - [Configuration](#configuration)
    - [Prerequisites](#prerequisites)
    - [Activation](#activation)
    - [API Secret Key](#api-secret-key)
    - [Minimum amount](#minimum-amount)
  - [Testing](#testing)
    - [Requirements](#requirements)
    - [Starting Prestashop](#starting-prestashop)
    - [Testing the module](#testing-the-module)
    - [Stopping Prestashop](#stopping-prestashop)
  - [Need help](#need-help)
    - [Documentation](#documentation)
    - [Feedback](#feedback)
    - [Support](#support)

## Overview

### Description

Kohortpay will turn your customers into your brands advocates by letting them invite their friends with a cashback : Pay less, together. Increase your sales and your conversion with KohortPay! That's awesoooome!

### Version
Current version is 1.0.0. See all [releases here](https://github.com/kohortpay/module-prestashop/releases).

### Licence
The module and this repository is under MIT License. 

### Compabilities and Restrictions
- Only FR and EN languages available.
- Only EUR currency available.
- Works and has been tested with Prestashop 1.7 and 8 (should work with Prestashop 1.6 but not tested, use at your own risk).

### Features
- Add a new payment method that you customer will love (Pay less, together)
- Redirect to an awesome and customized payment page (using you customer order details).
- Be able to enable/disable the module through the settings.
- Possibility to set minimun amount, under which the payment is disabled. 
- Easy way to switch live/test mode by filling you API secret key (sk or sk_test).
- Handle API errors (with more details if Prestashop is in debug mode).

### Benefits

- Coming soon...

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

## Configuration

### Prerequisites
- You should have a KohortPay account. If it's not the case, you can [register here](https://dashboard.kohortpay.com/sign-up).
- You should have installed the module on your Prestashop instance and have access to its settings page.

### Activation

You can display or hide the KohortPay payment method from you checkout page using this configuration (enabled/disabled).

### API Secret Key

Found in KohortPay Dashboard > Developer settings > API Keys.
Start with sk_ or sk_test (for test mode).

### Minimum amount

You can define here the total order minimum amount to display the KohortPay payment method (by default, it's 30€).

## Testing

If you want to test or make a demonstration of the KohortPay module on a fresh Prestashop installation, please read the following instruction:

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
6. Go back to the frontend (http://localhost/) and proceed to the checkout with enough products in your cart (to reach minimum amount in settings).
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
- [Help Center](https://support.kohortpay.com/)

### Feedback
If you have any idea or suggestion to improve our solution or the module, you can send an email to feedback@kohortpay.com. You can also check our [roadmap here](https://roadmap.kohortpay.com/tabs/1-under-consideration).

### Support
If you need help, please contact our support team by sending an email to support@kohortpay.com.

**NB**: We don't provide any SLA on our support response time (best effort). 