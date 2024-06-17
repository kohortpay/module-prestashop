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

class KohortpayWebhookModuleFrontController extends ModuleFrontController
{
  /**
   * Webhook controller to receive event notifications from KohortPay
   *
   * @see FrontController::postProcess()
   */
  public function postProcess()
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
      case 'payment_group.succeeded':
        /*
         {"payload":{"data":{"canceledAt":null,"completedAt":"2024-06-14T08:41:00.026Z","createdAt":"2024-06-14T08:30:25.507Z","createdBy":"cus_9c3b9aecb6cddb","creatorEmail":"aymeric@kohort.eu","customerId":"cus_9c3b9aecb6cddb","expiresAt":"2024-06-14T08:40:25.504Z","id":"pg_6be125f06f79d2","level":1,"livemode":false,"metadata":null,"midExpireAt":"2024-06-14T08:35:25.504Z","organizationId":"org_0434fa776a13cd","participantsToUnlock":2,"paymentGroupSettings":{"createdAt":"2024-06-14T08:30:25.529Z","createdBy":"system","discountLevels":[{"createdAt":"2024-06-14T08:30:25.530Z","id":"dlev_125f06f79d2ebb","level":1,"participantsToUnlock":2,"paymentGroupSettingsId":"pgset_e125f06f79d2eb","value":10},{"createdAt":"2024-06-14T08:30:25.530Z","id":"dlev_25f06f79d2ebb5","level":2,"participantsToUnlock":5,"paymentGroupSettingsId":"pgset_e125f06f79d2eb","value":15},{"createdAt":"2024-06-14T08:30:25.530Z","id":"dlev_5f06f79d2ebb57","level":3,"participantsToUnlock":10,"paymentGroupSettingsId":"pgset_e125f06f79d2eb","value":20}],"discountType":"PERCENTAGE","id":"pgset_e125f06f79d2eb","livemode":false,"maxParticipants":15,"minPurchaseValue":3000,"minutesDuration":10,"organizationId":null,"paymentGroupId":"pg_6be125f06f79d2","updatedAt":"2024-06-14T08:30:25.529Z","updatedBy":"system"},"paymentGroupSettingsId":"pgset_e125f06f79d2eb","paymentIntents":[{"amount":6444,"amountCaptured":null,"amountCashback":null,"applicationFeeAmount":null,"canceledAt":null,"checkoutSessionId":"cs_34d2c6be125f06","clientReferenceId":"ZEHQAKIAY","createdAt":"2024-06-14T08:30:25.468Z","createdBy":"system","currency":"EUR","customerEmail":"aymeric@kohort.eu","customerId":"cus_9c3b9aecb6cddb","id":"pi_c6be125f06f79d","livemode":false,"metadata":null,"organizationId":"org_0434fa776a13cd","paymentGroupId":"pg_6be125f06f79d2","riskLevel":"LOW","status":"SUCCEEDED","stripeClientSecret":null,"stripeId":null,"stripeRiskLevel":null,"updatedAt":"2024-06-14T08:30:25.528Z","updatedBy":"system"},{"amount":9400,"amountCaptured":null,"amountCashback":null,"applicationFeeAmount":1128,"canceledAt":null,"checkoutSessionId":"cs_f06f79d2ebb579","clientReferenceId":"ZHIVSJIWS","createdAt":"2024-06-14T08:31:28.472Z","createdBy":"system","currency":"EUR","customerEmail":"aymeric+cl@kohort.eu","customerId":"cus_99eece8d103e10","id":"pi_79d2ebb579738d","livemode":false,"metadata":null,"organizationId":"org_0434fa776a13cd","paymentGroupId":"pg_6be125f06f79d2","riskLevel":"HIGH","status":"SUCCEEDED","stripeClientSecret":null,"stripeId":null,"stripeRiskLevel":null,"updatedAt":"2024-06-14T08:31:28.544Z","updatedBy":"system"}],"reminderEmailSent":false,"shareId":"KHTPAY-test-BE125F06","status":"COMPLETED","updatedAt":"2024-06-14T08:41:00.027Z","updatedBy":"system","value":10},"type":"payment_group.succeeded"}} */

        // If only one payment intent is present, it means the group is emppty and do not apply cashback
        if (count($request['data']['paymentIntents']) === 1) {
          http_response_code(200);
          $this->LogWebhookMessage('Group empty, no cashback applied', 1);
          die('Group empty, no cashback applied');
        }

        foreach ($request['data']['paymentIntents'] as $paymentIntent) {
          //if ($paymentIntent['amountCashback']) {
          $order_id = $paymentIntent['clientReferenceId'];
          $order = new Order((int) $order_id);
          if ($order) {
            /*$order->addOrderPayment(
              $paymentIntent['amountCashback'],
              null,
              null,
              'Cashback',
              null,
              null,
              $order->id_currency
            );*/
            $order->setCurrentState(Configuration::get('PS_OS_REFUND'));
          }

          //}
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
