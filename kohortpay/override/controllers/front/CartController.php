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
      $code = trim(Tools::getValue('discount_name'));
      if (substr($code, 0, 3) === 'KHT') {
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

      $referralGroup = json_decode($response->getBody()->getContents(), true);

      $cashbackType = $referralGroup['discount_type'] ?? 'PERCENTAGE';
      $cashbackValue = $referralGroup['current_discount_level']['value'] ?? 0.0;

      $this->saveReferralDetailsInDB($code, $cashbackType, $cashbackValue);

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

        $errorCode = $errorResponse['error']['code'] ?? null;
        $this->errors[] = Module::getInstanceByName('kohortpay')->getErrorMessageByCode($errorCode);
        return;
      }
    }
  }

  /**
   * Save referral code and cashback amount in database.
   */
  protected function saveReferralDetailsInDB($code, $cashbackType = 'PERCENTAGE', $cashbackValue = 0.0)
  {
    if (!$this->context->cart->id || !$code || !$cashbackType || $cashbackValue === 0.0) {
      return;
    }

    $sql = new DbQuery();
    $sql->select('id_cart');
    $sql->from('referral_cart');
    $sql->where('id_cart = ' . (int) $this->context->cart->id);

    if (Db::getInstance()->getValue($sql)) {
      Db::getInstance()->update(
        'referral_cart',
        [
          'share_id' => pSQL($code),
          'cashback_type' => pSQL($cashbackType),
          'cashback_value' => (float) $cashbackValue,
        ],
        'id_cart = ' . (int) $this->context->cart->id
      );
    } else {
      Db::getInstance()->insert('referral_cart', [
        'id_cart' => (int) $this->context->cart->id,
        'share_id' => pSQL($code),
        'cashback_type' => pSQL($cashbackType),
        'cashback_value' => (float) $cashbackValue,
      ]);
    }
  }
}
