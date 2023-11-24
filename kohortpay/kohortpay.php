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
    exit;
}

class Kohortpay extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'kohortpay';
        $this->tab = 'payments_gateways';
        $this->version = '0.1.0';
        $this->author = 'KohortPay';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('KohortPay');
        $this->description = $this->l('Social payment method : Pay less, together. Turn your customer into your brand advocates.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall KohortPay ?');

        $this->limited_currencies = array('EUR');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (extension_loaded('curl') == false)
        {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        Configuration::updateValue('KOHORTPAY_LIVE_MODE', false);
        Configuration::updateValue('KOHORTPAY_API_SECRET_KEY', null);
        Configuration::updateValue('KOHORTPAY_MINIMUM_AMOUNT', 30);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        Configuration::deleteByName('KOHORTPAY_LIVE_MODE');
        Configuration::deleteByName('KOHORTPAY_API_SECRET_KEY');
        Configuration::deleteByName('KOHORTPAY_MINIMUM_AMOUNT');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitKohortpayModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'KOHORTPAY_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'KOHORTPAY_API_SECRET_KEY',
                        'label' => $this->l('API Secret Key'),
                        'desc' => $this->l('Found in KohortPay Dashboard > Developer settings. Start with sk_'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'KOHORTPAY_MINIMUM_AMOUNT',
                        'class' => 'fixed-width-xs',
                        'size' => 20,
                        'prefix' => $this->context->currency->iso_code,
                        'label' => $this->l('Minimum amount'),
                        'desc' => $this->l('Minimum amount to enable KohortPay payment method'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'KOHORTPAY_LIVE_MODE' => Configuration::get('KOHORTPAY_LIVE_MODE', true),
            'KOHORTPAY_API_SECRET_KEY' => Configuration::get('KOHORTPAY_API_SECRET_KEY', null),
            'KOHORTPAY_MINIMUM_AMOUNT' => Configuration::get('KOHORTPAY_MINIMUM_AMOUNT', 0),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array|null
     */
    public function hookPaymentOptions($params)
    {
        if (!Configuration::get('KOHORTPAY_LIVE_MODE')) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        if ($params['cart']->getOrderTotal() < Configuration::get('KOHORTPAY_MINIMUM_AMOUNT')) {
            return;
        }

        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/kohortpay_logo_payment.png'))
            ->setCallToActionText($this->l('Pay, share and save up to 20% off'))
            ->setAdditionalInformation($this->l('Save money and so does your friend, from the first friend you invite.'))
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true));

        return [
            $option
        ];
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
}
