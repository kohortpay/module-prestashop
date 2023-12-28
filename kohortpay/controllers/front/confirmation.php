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

if (!defined('_PS_VERSION_')) {
  exit();
}

class KohortpayConfirmationModuleFrontController extends ModuleFrontController
{
  public function postProcess()
  {
    if (
      Tools::isSubmit('cart_id') == false ||
      Tools::isSubmit('secure_key') == false
    ) {
      return false;
    }

    $cart_id = Tools::getValue('cart_id');
    $secure_key = Tools::getValue('secure_key');

    $cart = new Cart((int) $cart_id);
    $customer = new Customer((int) $cart->id_customer);

    /**
     * Converting cart into a valid order
     */
    $payment_status = Configuration::get('PS_OS_PAYMENT');
    $message = $this->module->l('Payment was authorized by KohortPay');
    $module_name = $this->module->displayName;
    $currency_id = (int) Context::getContext()->currency->id;

    $this->module->validateOrder(
      $cart_id,
      $payment_status,
      $cart->getOrderTotal(),
      $module_name,
      $message,
      [],
      $currency_id,
      false,
      $secure_key
    );

    /**
     * If the order has been validated we try to retrieve it
     */
    $order_id = Order::getOrderByCartId((int) $cart->id);

    if ($order_id && $secure_key == $customer->secure_key) {
      /**
       * The order has been placed so we redirect the customer on the confirmation page.
       */
      $module_id = $this->module->id;
      Tools::redirect(
        'index.php?controller=order-confirmation&id_cart=' .
          $cart_id .
          '&id_module=' .
          $module_id .
          '&id_order=' .
          $order_id .
          '&key=' .
          $secure_key
      );
    } else {
      /*
       * An error occured and is shown on a new page.
       */
      $this->context->smarty->assign([
        'errors' => [
          $this->module->l(
            'An error occured to validate your order. Please contact the merchant to have more informations'
          ),
        ],
      ]);

      return $this->setTemplate(
        'module:kohortpay/views/templates/front/error.tpl'
      );
    }
  }
}
