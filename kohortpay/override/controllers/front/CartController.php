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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CartController extends CartControllerCore
{
  /**
   * Override postProcess method to add custom logic.
   */
  public function postProcess()
  {
    if (!Configuration::get('KOHORTREF_LIVE_MODE')) {
      parent::postProcess();
      return;
    }

    if (Tools::getIsset('addDiscount')) {
      if (!($code = trim(Tools::getValue('discount_name')))) {
        $this->errors[] = $this->trans('You must enter a voucher code.', [], 'Shop.Notifications.Error');
      } elseif (!Validate::isCleanHtml($code)) {
        $this->errors[] = $this->trans('The voucher code is invalid.', [], 'Shop.Notifications.Error');
      } elseif (substr($code, 0, 3) === 'KHT') {
        $this->validateReferralCode($code);
        return;
      }
    }

    parent::postProcess();
  }

  /**
   * Make API POST call to send order to KohortRef.
   */
  protected function validateReferralCode($code)
  {
    $additionnalInfo = [
      'amount' => round($this->context->cart->getOrderTotal() * 100),
    ];

    $client = new Client();
    try {
      $response = $client->post('https://api.kohortpay.com/payment-groups/' . $code . '/validate', [
        'headers' => [
          'Authorization' => 'Bearer ' . Configuration::get('KOHORTPAY_API_SECRET_KEY'),
        ],
        'json' => $additionnalInfo,
      ]);

      $cashbackAmount = json_decode($response->getBody()->getContents(), true)['cashback_amount'] ?? 5.0;
      $this->saveReferralDetailsInDB($code, $cashbackAmount);

      return true;
    } catch (ClientException $e) {
      if ($e->hasResponse()) {
        $errorResponse = json_decode(
          $e
            ->getResponse()
            ->getBody()
            ->getContents(),
          true
        );

        // If the error message is present in the response, we display it.
        $minimumAmount = Tools::displayPrice(Configuration::get('KOHORTREF_MINIMUM_AMOUNT') ?? 30.0);
        $defaultSuffixErrorMessage = $this->trans(
          'Complete a purchase of at least %s with a credit card to generate a referral code and get cashback on your order by sharing it.',
          [$minimumAmount],
          'kohortpay'
        );
        $errorCode = $errorResponse['error']['code'] ?? null;
        if ($errorCode) {
          $errorMessage = '';
          switch ($errorCode) {
            case 'AMOUNT_TOO_LOW':
              $errorMessage = 'The cart amount is too low to use this referral code.';
              break;
            case 'COMPLETED_EXPIRED_CANCELED':
              $errorMessage = 'Unfortunately, the referral period of the cohort has ended.';
              break;
            case 'MAX_PARTICIPANTS_REACHED':
              $errorMessage = 'Unfortunately, the maximum number of people in the cohort has been reached.';
              break;
            case 'EMAIL_ALREADY_USED':
              $errorMessage = 'The email address has already been used to join the cohort.';
              break;
            case 'NOT_FOUND':
              $errorMessage = 'The referral code is unknown or not found.';
              break;
            default:
              $errorMessage = 'The referral code is invalid.';
              break;
          }

          $this->errors[] = $this->trans($errorMessage, [], 'kohortpay') + ' ' + $defaultSuffixErrorMessage;
          return;
        }

        // If any error occurs, we display a generic error message.
        $this->errors[] = $this->trans('The referral code is invalid.', [], 'kohortpay');
        return;
      }
    }
  }

  /**
   * Save referral code and cashback amount in database.
   */
  protected function saveReferralDetailsInDB($code, $cashbackAmount = 0.0)
  {
    $sql = new DbQuery();
    $sql->select('id_cart');
    $sql->from('referral_cart');
    $sql->where('id_cart = ' . (int) $this->context->cart->id);

    if (Db::getInstance()->getValue($sql)) {
      Db::getInstance()->update(
        'referral_cart',
        [
          'share_id' => pSQL($code),
          'cashback_amount' => (float) $cashbackAmount,
        ],
        'id_cart = ' . (int) $this->context->cart->id
      );
    } else {
      Db::getInstance()->insert('referral_cart', [
        'id_cart' => (int) $this->context->cart->id,
        'share_id' => pSQL($code),
        'cashback_amount' => (float) $cashbackAmount,
      ]);
    }
  }
}
