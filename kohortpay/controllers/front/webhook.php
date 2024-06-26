<?php
namespace KohortPay\Controller;

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

use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Order\Command\IssueStandardRefundCommand;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class KohortpayWebhookModuleFrontController extends FrameworkBundleAdminController
{
  /**
   * @var CommandBusInterface
   */
  private $commandBus;

  /**
   * @param CommandBusInterface $commandBus
   */
  public function __construct(CommandBusInterface $commandBus)
  {
    $this->commandBus = $commandBus;
  }

  /**
   * Webhook controller to receive event notifications from KohortPay
   *
   * @see FrontController::postProcess()
   */
  public function run()
  {
    $request = json_decode(file_get_contents('php://input'), true);
    $this->LogWebhookMessage('Webhook received : ' . json_encode($request), 1);

    if (!isset($request['data'])) {
      http_response_code(400);
      $this->LogWebhookMessage('Invalid request', 3);
      die('Invalid request');
    }

    switch ($request['type']) {
      /**
       * Converting cart into a valid order
       */
      case 'payment_intent.succeeded':
        $cart_id = $request['data']['clientReferenceId'];
        $cart = new Cart((int) $cart_id);
        $customer = new Customer((int) $cart->id_customer);
        $payment_status = Configuration::get('PS_OS_PAYMENT');
        $message = $this->module->l('Payment was authorized by KohortPay');
        $module_name = $this->module->displayName;
        $currency_id = $cart->id_currency;

        // Check if order has already been validated
        if (Validate::isLoadedObject($cart) && $cart->OrderExists()) {
          http_response_code(200);
          $this->LogWebhookMessage('Order already exists', 1);
          die('Order already exists');
        }

        // Validate order
        $this->module->validateOrder(
          $cart_id,
          $payment_status,
          $cart->getOrderTotal(),
          $module_name,
          $message,
          [],
          $currency_id,
          false,
          $customer->secure_key
        );

        break;
      /**
       * Refund order with cashback
       */
      case 'payment_intent.cashback_sent':
        if ($request['data']['amountCashback']) {
          $order_id = $request['data']['clientReferenceId'];
          $order = new Order((int) $order_id);
          if ($order) {
            $refunds = [];
            // Divde amountCashback by number of products in order and create refund for each product
            $orderDetails = $order->getOrderDetailList();
            $amount = $request['data']['amountCashback'] / 100;
            $amountPerProduct = $amount / count($orderDetails);
            foreach ($orderDetails as $orderDetail) {
              $refunds[$orderDetail['id_order_detail']] = [
                'quantity' => 1,
                'amount' => $amountPerProduct,
              ];
            }

            $command = new IssueStandardRefundCommand($order_id, $refunds, false, false, true, false, 0);
            $this->commandBus->handle($command);
          }
        }

        break;
      default:
        http_response_code(400);
        $this->LogWebhookMessage('Invalid event type', 3);
        die('Invalid event type');
    }

    http_response_code(200);
    $this->LogWebhookMessage('Event received', 1);
    die('Event received');
  }

  /**
   * Log webhook messages
   *
   * @param string $message
   * @param int $severity
   */
  protected function LogWebhookMessage($message, $severity = 3)
  {
    PrestaShopLogger::addLog($message, $severity, null, 'KohortPay', 0, true);
  }
}
