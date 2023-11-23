<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    KohortPay <contact@kohortpay.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
class KohortpayRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        $languageCode = explode('-', Context::getContext()->language->language_code);
        $json['locale'] = $languageCode[0] . '_' . strtoupper($languageCode[1]);

        $json['successUrl'] = $this->context->link->getModuleLink('kohortpay', 'confirmation', array('action' => 'success', 'cart_id' => Context::getContext()->cart->id, 'secure_key' => Context::getContext()->customer->secure_key));


        $json['websiteOrderURL'] = Configuration::get('PS_SHOP_DOMAIN');

        $json['amountTotal'] = Context::getContext()->cart->getOrderTotal() * 100;
        $json['lineItems'] = array();
        foreach (Context::getContext()->cart->getProducts() as $product) {
            $json['lineItems'][] = array(
                'name' => $product['name'],
                'price' => $product['price'] * 100,
                'quantity' => $product['cart_quantity'],
                'type' => 'PRODUCT',
                'image_url' => $this->context->link->getImageLink($product['link_rewrite'], $product['id_image'], 'home_default'),
            );
        }
        foreach (Context::getContext()->cart->getCartRules() as $cartRule) {
            $json['lineItems'][] = array(
                'name' => $cartRule['name'],
                'price' => $cartRule['value_real'] * -100,
                'quantity' => 1,
                'type' => 'DISCOUNT',
            );
        }
        $json['lineItems'][] = array(
            'name' => 'Shipping',
            'price' => Context::getContext()->cart->getTotalShippingCost() * 100,
            'quantity' => 1,
            'type' => 'SHIPPING',
        );

        $json['customerFirstName'] = Context::getContext()->customer->firstname;
        $json['customerLastName'] = Context::getContext()->customer->lastname;
        $json['customerEmail'] = Context::getContext()->customer->email;

        $client = new Client();
        try {
            $response = $client->post(
                "https://api.kohortpay.dev/checkout-sessions/",
                [
                    'json' => $json,
                    'auth' => [
                        Configuration::get('KOHORTPAY_API_SECRET_KEY'),
                        ''
                    ]
                ],
            );
            $checkoutSession = json_decode($response->getBody()->getContents(), true);
            if (isset($checkoutSession['url'])) {
                Tools::redirect($checkoutSession['url']);
            }
        } catch (ClientException $e) {
            if (_PS_MODE_DEV_) {
                var_dump($json);
            }
            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                if (isset($errorResponse['error']['message'])) {
                    return $this->displayError($errorResponse['error']['message']);
                }
            }
            return $this->displayError($this->module->l('An error occurred while trying to redirect the customer. Please contact the merchant to have more informations'));
        }

        die();

        /*
         * Oops, an error occured.
         */
        if (Tools::getValue('action') == 'error') {
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {
            $this->context->smarty->assign(
                array(
                    'cart_id' => Context::getContext()->cart->id,
                    'secure_key' => Context::getContext()->customer->secure_key,
                )
            );

            return $this->setTemplate('module:kohortpay/views/templates/front/redirect.tpl');
        }
    }

    protected function displayError($errors)
    {
        if (!is_array($errors)) {
            $errors = array($errors);
        }

        $this->context->smarty->assign(
            array(
                'errors' => $errors,
            )
        );

        return $this->setTemplate('module:kohortpay/views/templates/front/error.tpl');
    }
}
