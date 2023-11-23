# Official KohortPay Prestashop 1.7 module

## Table of content

- [Official KohortPay Prestashop 1.7 module](#official-kohortpay-prestashop-17-module)
  - [Table of content](#table-of-content)
  - [Overview](#overview)
    - [Description](#description)
    - [Features](#features)
    - [Compabilities](#compabilities)
  - [Installation](#installation)
    - [Using Prestashop admin](#using-prestashop-admin)
    - [Using Symfony CLI](#using-symfony-cli)
    - [Using ZIP](#using-zip)
    - [Using Composer](#using-composer)
  - [Testing](#testing)
    - [Requirements](#requirements)
    - [Starting Prestashop](#starting-prestashop)
    - [Testing the module](#testing-the-module)
    - [Stopping Prestashop](#stopping-prestashop)

## Overview

KohortPay Payments official module will offer your customers the choice to pay in parts, pay later, pay now, pay with Sofort or pay in instalments. Increase your sales and your conversion with KohortPay! That's smoooth!

### Description

xxxxxx

### Features

- XX
- YYY

### Compabilities

**Important**: This is working

## Installation

### Using Prestashop admin

1. Connect to your admin
2.

### Using Symfony CLI

### Using ZIP

Moving the directory /kohortpay of this repository to

### Using Composer

Coming soon

## Testing

If you want to test or make a demonstration of the KohortPay module on a fresh Prestashop installation, please read the following instruction:

### Requirements

- Docker desktop:
  - [Mac install instruction](https://docs.docker.com/desktop/install/mac-install/)
  - [Windows install instruction](https://docs.docker.com/desktop/install/windows-install/)
- Docker Compose: Already included in Docker Desktop.

### Starting Prestashop

1. Start your Docker desktop application: [More info](https://www.docker.com/blog/getting-started-with-docker-desktop/)
2. Clone the repository and go inside the directory:
   ```
   git clone git@github.com:kohortpay/module-prestashop.git
   cd module-prestashop
   ```
3. Pick the prestashop version `here`
4. Start the docker stack:
   ```
   docker-compose up -d --build
   ```
5. Wait for container to be up (~1 minute), then visit [http://localhost/](http://localhost/)
6. Your prestashop is UP. Enjoy!

### Testing the module

1.  Go to local/admi
2.  log init with crenditial (Login: user@example.com, Password : bitnami1)
3.  Go to
4.  Click on upload and select kohortpayZip
5.  Enable

6.  Connect

### Stopping Prestashop

When your tests are over, you can stop and destroy everything with the following command:

```
docker-compose down --volumes
```
