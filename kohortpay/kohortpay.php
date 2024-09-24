<?php
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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

if (!defined('_PS_VERSION_')) {
  exit();
}

class Kohortpay extends PaymentModule
{
  protected $config_form = false;

  public function __construct()
  {
    $this->name = 'kohortpay';
    $this->tab = 'payments_gateways';
    $this->version = '1.0.4';
    $this->author = 'KohortPay';
    $this->need_instance = 0;
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('KohortPay');
    $this->description = $this->l(
      'Social payment method : Pay less, together. Turn your customer into your brand advocates.'
    );

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall KohortPay ?');

    $this->limited_currencies = ['EUR', 'USD'];

    $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

    $this->module_key = 'f3f8c71200e9d7a10a7bf766873bde81';
  }

  /**
   * Don't forget to create update methods if needed:
   * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
   */
  public function install()
  {
    if (extension_loaded('curl') == false) {
      $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
      return false;
    }

    Configuration::updateValue('KOHORTREF_LIVE_MODE', false);
    Configuration::updateValue('KOHORTPAY_API_SECRET_KEY', '');
    Configuration::updateValue('KOHORTPAY_MINIMUM_AMOUNT', 30);
    Configuration::updateValue('KOHORTPAY_DEBUG_MODE', false);

    include dirname(__FILE__) . '/sql/install.php';

    $hooks = ['actionPaymentConfirmation', 'actionPresentCart'];

    return parent::install() && $this->registerHook($hooks);
  }

  public function uninstall()
  {
    /**
     * In some cases you should not drop the tables.
     * Maybe the merchant will just try to reset the module
     * but does not want to loose all of the data associated to the module.
     */

    return parent::uninstall();
  }

  /**
   * Load the configuration form
   */
  public function getContent()
  {
    if (((bool) Tools::isSubmit('submitKohortpayModule')) == true) {
      $this->postProcess();
    }

    $this->context->smarty->assign('module_dir', $this->_path);

    return $this->renderForm();
  }

  /**
   * Create the form that will be displayed in the configuration of your module.
   */
  protected function renderForm()
  {
    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitKohortpayModule';
    $helper->currentIndex =
      $this->context->link->getAdminLink('AdminModules', false) .
      '&configure=' .
      $this->name .
      '&tab_module=' .
      $this->tab .
      '&module_name=' .
      $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');

    $helper->tpl_vars = [
      'fields_value' => $this->getConfigFormValues() /* Add values for your inputs */,
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    ];

    return $helper->generateForm([$this->getConfigForm()]);
  }

  /**
   * Create the structure of your form.
   */
  protected function getConfigForm()
  {
    return [
      'form' => [
        'legend' => [
          'title' => $this->l('Referral program settings (with your own payment methods)'),
          'icon' => 'icon-cogs',
        ],
        'input' => [
          [
            'type' => 'switch',
            'label' => $this->l('Activate'),
            'name' => 'KOHORTREF_LIVE_MODE',
            'is_bool' => true,
            'desc' => $this->l('Must be enabled to let your customers refer.'),
            'values' => [
              [
                'id' => 'active_on',
                'value' => true,
                'label' => $this->l('Enabled'),
              ],
              [
                'id' => 'active_off',
                'value' => false,
                'label' => $this->l('Disabled'),
              ],
            ],
          ],
          [
            'type' => 'password',
            'name' => 'KOHORTPAY_API_SECRET_KEY',
            'class' => 'fixed-width-xl',
            'label' => $this->l('API Secret Key'),
            'desc' => $this->l('Found in Dashboard > Developer settings. Start with sk_ or sk_test (for test mode).'),
          ],
          [
            'type' => 'text',
            'name' => 'KOHORTPAY_MINIMUM_AMOUNT',
            'class' => 'fixed-width-md',
            'prefix' => $this->context->currency->iso_code,
            'label' => $this->l('Minimum amount'),
            'desc' => $this->l('Minimum total order amount to refer.'),
          ],
          [
            'type' => 'switch',
            'label' => $this->l('Debug mode'),
            'name' => 'KOHORTPAY_DEBUG_MODE',
            'is_bool' => true,
            'desc' => $this->l('Add additional logs to help you debug.'),
            'values' => [
              [
                'id' => 'active_on',
                'value' => true,
                'label' => $this->l('Enabled'),
              ],
              [
                'id' => 'active_off',
                'value' => false,
                'label' => $this->l('Disabled'),
              ],
            ],
          ],
        ],
        'submit' => [
          'title' => $this->l('Save'),
        ],
      ],
    ];
  }

  /**
   * Set values for the inputs.
   */
  protected function getConfigFormValues()
  {
    $configFormValues = [
      'KOHORTREF_LIVE_MODE' => Configuration::get('KOHORTREF_LIVE_MODE', false),
      'KOHORTPAY_DEBUG_MODE' => Configuration::get('KOHORTPAY_DEBUG_MODE', false),
      'KOHORTPAY_API_SECRET_KEY' => Configuration::get('KOHORTPAY_API_SECRET_KEY', null),
      'KOHORTPAY_MINIMUM_AMOUNT' => Configuration::get('KOHORTPAY_MINIMUM_AMOUNT', 30),
    ];

    return $configFormValues;
  }

  /**
   * Save form data.
   */
  protected function postProcess()
  {
    $form_values = $this->getConfigFormValues();

    // Validate KOHORTPAY_MINIMUM_AMOUNT field is a valid price
    if (!Validate::isPrice(Tools::getValue('KOHORTPAY_MINIMUM_AMOUNT'))) {
      $this->context->controller->errors[] = $this->l('Invalid value for minimum amount.');
      return false;
    }

    // KOHORTPAY_API_SECRET_KEY is required if KohortPay or KohortRef live mode is enabled
    if (
      Tools::getValue('KOHORTREF_LIVE_MODE') &&
      !Tools::getValue('KOHORTPAY_API_SECRET_KEY') &&
      !Configuration::get('KOHORTPAY_API_SECRET_KEY')
    ) {
      $this->context->controller->errors[] = $this->l('API Secret Key is required.');
      return false;
    }

    $kohortRefPaymentMethods = [];
    foreach (array_keys($form_values) as $key) {
      // If KOHORTPAY_API_SECRET_KEY value is empty but configuration value is not, use the configuration value
      if ($key == 'KOHORTPAY_API_SECRET_KEY' && !Tools::getValue($key) && Configuration::get($key)) {
        Configuration::updateValue($key, Configuration::get($key));
        continue;
      }

      Configuration::updateValue($key, Tools::getValue($key));
    }

    $this->context->controller->confirmations[] = $this->l('Settings updated');
  }

  /*
   * Call KohortPay API to send order when payment is confirmed
   */
  public function hookActionPaymentConfirmation($params)
  {
    $order = new Order($params['id_order']);
    $cart = new Cart((int) $order->id_cart);
    $customer = new Customer((int) $order->id_customer);

    if (!Configuration::get('KOHORTREF_LIVE_MODE')) {
      $this->LogOrderMessage('KohortRef is not enabled.', $order->id, 2);
      return;
    }

    if (!Configuration::get('KOHORTPAY_API_SECRET_KEY')) {
      $this->LogOrderMessage('API Secret Key is not set.', $order->id, 2);
      return;
    }

    $orderCurrencyCode = (new Currency($order->id_currency))->iso_code;
    if (!in_array($orderCurrencyCode, $this->limited_currencies)) {
      $this->LogOrderMessage(
        'Order currency (' .
          $orderCurrencyCode .
          ') is not supported. Supported currencies : ' .
          implode(', ', $this->limited_currencies),
        $order->id,
        2
      );
      return;
    }

    if ($order->total_paid < Configuration::get('KOHORTPAY_MINIMUM_AMOUNT')) {
      $this->LogOrderMessage(
        'Order total (' .
          $order->total_paid .
          ') is less than minimum amount : ' .
          Configuration::get('KOHORTPAY_MINIMUM_AMOUNT'),
        $order->id,
        2
      );
      return;
    }

    $orderJson = $this->getOrderJson($cart, $order, $customer);
    $this->sendOrder($order->id, $orderJson);
  }

  /**
   * Enable vouchers if KohortRef is enabled
   */
  public function hookActionPresentCart(array $params)
  {
    if (Configuration::get('KOHORTREF_LIVE_MODE')) {
      $params['presentedCart']['vouchers']['allowed'] = 1;
    }

    if ($params['cart']->getOrderTotal() < Configuration::get('KOHORTPAY_MINIMUM_AMOUNT')) {
      return $params;
    }

    $sql = new DbQuery();
    $sql->select('cashback_value, cashback_type');
    $sql->from('referral_cart');
    $sql->where('id_cart = ' . (int) $this->context->cart->id);

    $result = Db::getInstance()->getRow($sql);
    if (!$result) {
      return $params;
    }
    $cashbackType = $result['cashback_type'];
    $cashbackValue = $result['cashback_value'];

    $cashbackAmount = 0;
    if ($cashbackType && $cashbackValue) {
      if ($cashbackType == 'PERCENTAGE') {
        $cashbackAmount = ($params['cart']->getOrderTotal() * $cashbackValue) / 100;
      } else {
        $cashbackAmount = $cashbackValue;
      }
    }

    if ($cashbackAmount !== 0) {
      $params['presentedCart']['vouchers']['added'][] = [
        'id_cart_rule' => 0,
        'name' => $this->l('Cashback unlocked'),
        'free_shipping' => false,
        'reduction_formatted' => Tools::displayPrice($cashbackAmount),
      ];
    }

    return $params;
  }

  /**
   * Build and get order JSON object to send to the API.
   */
  protected function getOrderJson($cart, $order, $customer)
  {
    // Customer information
    $json['customerFirstName'] = $customer->firstname;
    $json['customerLastName'] = $customer->lastname;
    $json['customerEmail'] = $customer->email;
    $customerAddress = new Address($order->id_address_delivery);
    $json['customerPhoneNumber'] = $customerAddress->phone;

    // Get order total with taxes
    $json['amountTotal'] = $this->cleanPrice($order->total_paid);

    // Get customer locale
    //$json['locale'] = $this->context->language->language_code;

    // Line items
    $json['lineItems'] = [];
    // Products
    foreach ($cart->getProducts() as $product) {
      $json['lineItems'][] = [
        'name' => $this->cleanString($product['name']),
        'description' => $this->cleanString($product['description_short']),
        'price' => $this->cleanPrice($product['price_wt']),
        'quantity' => $product['cart_quantity'],
        'type' => 'PRODUCT',
        'image_url' => $this->context->link->getImageLink(
          $product['link_rewrite'],
          $product['id_image'],
          ImageType::getFormattedName('home')
        ),
      ];
    }
    // Discounts
    foreach ($cart->getCartRules() as $cartRule) {
      $json['lineItems'][] = [
        'name' => $this->cleanString($cartRule['name']),
        'price' => $this->cleanPrice($cartRule['value_real']) * -1,
        'quantity' => 1,
        'type' => 'DISCOUNT',
      ];
    }
    // Shipping
    $json['lineItems'][] = [
      'name' => $this->getCarrierName($cart->id_carrier),
      'price' => $this->cleanPrice($cart->getTotalShippingCost()),
      'quantity' => 1,
      'type' => 'SHIPPING',
    ];

    // @TODO : Add transaction_id to payment_client_reference_id
    //$transaction_id = OrderPayment::getByOrderReference($order->reference)->transaction_id;
    $json['client_reference_id'] = (string) $order->id;
    $json['payment_client_reference_id'] = (string) $order->id;
    $shareId = $this->getShareIdByIdCart($cart->id);
    if ($shareId) {
      $json['payment_group_share_id'] = $shareId;
    }

    // Metadata
    $json['metadata'] = [
      'order_id' => $order->id,
      'cart_id' => $cart->id,
      'customer_id' => $customer->id,
    ];

    return $json;
  }

  /**
   * Make API POST call to send order to KohortRef.
   */
  protected function sendOrder($orderId, $orderJson)
  {
    $this->LogOrderMessage('Sending order to KohortREF API with this JSON : ' . json_encode($orderJson), $orderId, 1);

    $client = new Client();
    try {
      $response = $client->post('https://api.kohortpay.com/checkout-sessions', [
        'headers' => [
          'Authorization' => 'Bearer ' . Configuration::get('KOHORTPAY_API_SECRET_KEY'),
        ],
        'json' => $orderJson,
      ]);

      $responseArray = json_decode($response->getBody()->getContents(), true);
      $this->LogOrderMessage(
        'Order sent to KohortPay API with success. Checkout session ID : ' . $responseArray['id'],
        $orderId,
        1
      );
    } catch (ClientException $e) {
      if ($e->hasResponse()) {
        $errorResponse = json_decode(
          $e
            ->getResponse()
            ->getBody()
            ->getContents(),
          true
        );
        if (isset($errorResponse['error']['message'])) {
          $this->LogOrderMessage($errorResponse['error']['message'], $orderId);
          return;
        }
      }
      $this->LogOrderMessage('An error occurred while trying to call KohortPay API to send order.', $orderId);
    }
  }

  /**
   * Get carrier name from id.
   */
  protected function getCarrierName($id_carrier)
  {
    $carrier = new Carrier($id_carrier);
    return $this->cleanString($carrier->name);
  }

  /**
   * Clean string to avoid XSS.
   */
  protected function cleanString($string)
  {
    $string = strip_tags($string);

    return $string;
  }

  /**
   * Round price to avoid price with more than 2 decimals.
   */
  protected function cleanPrice($price)
  {
    $price = $price * 100;
    $price = round($price, 0);

    return $price;
  }

  /**
   * Log order messages.
   */
  protected function LogOrderMessage($message, $orderId, $severity = 3)
  {
    // if severity inferior to 3 and KOHORTPAY_DEBUG_MODE is disabled, return
    if ($severity < 3 && !Configuration::get('KOHORTPAY_DEBUG_MODE')) {
      return;
    }

    if (is_array($message)) {
      $message = implode(', ', (array) $message);
    }

    PrestaShopLogger::addLog($message, $severity, null, 'Order', (int) $orderId, true);
  }

  /**
   * Make a query to referral_cart table to get share_id with parameter id_cart
   */
  public static function getShareIdByIdCart($id_cart)
  {
    $sql = 'SELECT share_id FROM ' . _DB_PREFIX_ . 'referral_cart WHERE id_cart = ' . (int) $id_cart;
    return Db::getInstance()->getValue($sql);
  }

  /**
   * Manage error messages
   */
  public function getErrorMessageByCode($errorCode)
  {
    $errorMessage = '';
    switch ($errorCode) {
      case 'AMOUNT_TOO_LOW':
        $errorMessage = $this->l('The cart amount is too low to use this referral code.');
        break;
      case 'COMPLETED_EXPIRED_CANCELED':
        $errorMessage = $this->l('Unfortunately, the referral period of the kohort has ended.');
        break;
      case 'MAX_PARTICIPANTS_REACHED':
        $errorMessage = $this->l('Unfortunately, the maximum number of people in the kohort has been reached.');
        break;
      case 'EMAIL_ALREADY_USED':
        $errorMessage = $this->l('The email address has already been used to join the kohort.');
        break;
      case 'NOT_FOUND':
        $errorMessage = $this->l('The referral code is unknown or not found.');
        break;
      default:
        $errorMessage = $this->l('The referral code is invalid.');
        break;
    }

    $minimumAmount = Tools::displayPrice(Configuration::get('KOHORTPAY_MINIMUM_AMOUNT'));
    $defaultSuffixErrorMessage =
      $this->l('Complete a purchase of at least ') .
      $minimumAmount .
      $this->l(' with a credit card to generate a referral code and get cashback on your order by sharing it.');

    return $errorMessage . ' ' . $defaultSuffixErrorMessage;
  }
}
