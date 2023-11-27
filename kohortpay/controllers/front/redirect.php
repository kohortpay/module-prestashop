<?php
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

/**
 * 2022-2024 KohortPay
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    KohortPay <contact@kohortpay.com>
 * @copyright 2022-2024 KohortPay
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
class KohortpayRedirectModuleFrontController extends ModuleFrontController
{
  /**
   * Make POST call to KohortPay API to generate checkout session and then redirect customer to the payment page.
   */
  public function postProcess()
  {
    $client = new Client();
    try {
      $response = $client->post('https://api.kohortpay.com/checkout-sessions', [
        'headers' => [
          'Authorization' =>
            'Bearer ' . Configuration::get('KOHORTPAY_API_SECRET_KEY'),
        ],
        'json' => $this->getCheckoutSessionJson(),
      ]);
      $checkoutSession = json_decode($response->getBody()->getContents(), true);
      if (isset($checkoutSession['url'])) {
        Tools::redirect($checkoutSession['url']);
      }
    } catch (ClientException $e) {
      if (_PS_MODE_DEV_) {
        var_dump($this->getCheckoutSessionJson());
      }
      if ($e->hasResponse()) {
        $errorResponse = json_decode(
          $e
            ->getResponse()
            ->getBody()
            ->getContents(),
          true
        );
        if (isset($errorResponse['error']['message'])) {
          return $this->displayError($errorResponse['error']['message']);
        }
      }
      return $this->displayError(
        $this->module->l(
          'An error occurred while trying to redirect the customer. Please contact the merchant to have more informations'
        )
      );
    }
  }

  /**
   * Build and get checkout session JSON object to send to the API.
   */
  protected function getCheckoutSessionJson()
  {
    // Customer information
    $json['customerFirstName'] = Context::getContext()->customer->firstname;
    $json['customerLastName'] = Context::getContext()->customer->lastname;
    $json['customerEmail'] = Context::getContext()->customer->email;

    // Return URLs
    $json['successUrl'] = $this->context->link->getModuleLink(
      'kohortpay',
      'confirmation',
      [
        'action' => 'success',
        'cart_id' => Context::getContext()->cart->id,
        'secure_key' => Context::getContext()->customer->secure_key,
      ]
    );
    $json['cancelUrl'] =
      Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'order';

    // Locale & currency
    $languageCode = explode(
      '-',
      Context::getContext()->language->language_code
    );
    $json['locale'] = $languageCode[0] . '_' . strtoupper($languageCode[1]);
    //$json['currency'] = Context::getContext()->currency->iso_code;

    // Order information
    $json['amountTotal'] = Context::getContext()->cart->getOrderTotal() * 100;

    // Line items
    $json['lineItems'] = [];
    // Products
    foreach (Context::getContext()->cart->getProducts() as $product) {
      $json['lineItems'][] = [
        'name' => $product['name'],
        'price' => $product['price'] * 100,
        'quantity' => $product['cart_quantity'],
        'type' => 'PRODUCT',
        'image_url' => $this->context->link->getImageLink(
          $product['link_rewrite'],
          $product['id_image'],
          'home_default'
        ),
      ];
    }
    // Discounts
    foreach (Context::getContext()->cart->getCartRules() as $cartRule) {
      $json['lineItems'][] = [
        'name' => $cartRule['name'],
        'price' => $cartRule['value_real'] * -100,
        'quantity' => 1,
        'type' => 'DISCOUNT',
      ];
    }
    // Shipping
    $json['lineItems'][] = [
      'name' => $this->getCarrierName(Context::getContext()->cart->id_carrier),
      'price' => Context::getContext()->cart->getTotalShippingCost() * 100,
      'quantity' => 1,
      'type' => 'SHIPPING',
    ];

    // Metadata
    $json['metadata'] = [
      'cart_id' => Context::getContext()->cart->id,
      'customer_id' => Context::getContext()->customer->id,
    ];

    return $json;
  }

  protected function getCarrierName($idCarrier)
  {
    $carrier = new Carrier($idCarrier);
    return $carrier->name;
  }

  /**
   * Display error messages.
   */
  protected function displayError($errors)
  {
    if (!is_array($errors)) {
      $errors = [$errors];
    }

    $this->context->smarty->assign([
      'errors' => $errors,
    ]);

    return $this->setTemplate(
      'module:kohortpay/views/templates/front/error.tpl'
    );
  }
}
