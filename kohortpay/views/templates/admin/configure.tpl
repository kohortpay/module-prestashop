{*
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
*}

<div class="panel">
	<div class="row kohortpay-header">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/kohortpay_logo_admin.png" class="col-xs-6 col-md-3 text-center" id="payment-logo" />
		<div class="col-xs-6 col-md-6 text-center text-muted">
			{l s='My Payment Module and PrestaShop have partnered to provide the easiest way for you to accurately calculate and file sales tax.' mod='kohortpay'}
		</div>
		<div class="col-xs-12 col-md-3 text-center">
			<a href="https://dashboard.kohortpay.com/sign-up" target="_blank" class="btn btn-primary" id="create-account-btn">{l s='Create an account' mod='kohortpay'}</a><br />
			{l s='Already have one?' mod='kohortpay'}<a href="https://dashboard.kohortpay.com/sign-in" target="_blank"> {l s='Log in' mod='kohortpay'}</a>
		</div>
	</div>

	<hr />
	
	<div class="kohortpay-content">
		<div class="row">
			<div class="col-md-5">
				<h2>{l s='Benefits' mod='kohortpay'}</h5>
				<ul class="ul-spaced">
					<li>
						<strong>{l s='It is fast and easy' mod='kohortpay'}:</strong>
						{l s='It is pre-integrated with PrestaShop, so you can configure it with a few clicks.' mod='kohortpay'}
					</li>
					
					<li>
						<strong>{l s='It is global' mod='kohortpay'}:</strong>
						{l s='Accept payments in XX currencies from XXX markets around the world.' mod='kohortpay'}
					</li>
					
					<li>
						<strong>{l s='It is trusted' mod='kohortpay'}:</strong>
						{l s='Industry-leading fraud an buyer protections keep you and your customers safe.' mod='kohortpay'}
					</li>
					
					<li>
						<strong>{l s='It is cost-effective' mod='kohortpay'}:</strong>
						{l s='There are no setup fees or long-term contracts. You only pay a low transaction fee.' mod='kohortpay'}
					</li>
				</ul>
			</div>
			
			<div class="col-md-2">
				<h2>{l s='Pricing' mod='kohortpay'}</h2>
				<dl class="list-unstyled">
					<dt>{l s='Payment Fees' mod='kohortpay'}</dt>
					<dd>{l s='1,25€ + 2%' mod='kohortpay'}</dd>
					<dt>{l s='Acquisition Fees' mod='kohortpay'}</dt>
					<dd>{l s='10%' mod='kohortpay'}</dd>
				</dl>
				<a href="#" onclick="javascript:return false;">(Detailed pricing here)</a>
			</div>
			
			<div class="col-md-5">
				<h2>{l s='How does it work?' mod='kohortpay'}</h2>
				<img src="{$module_dir|escape:'html':'UTF-8'}views/img/kohortpay_how_admin.png" />
			</div>
		</div>

		<hr />
		
		<div class="row">
			<div class="col-md-12">				
				<div class="row">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/kohortpay_cards_admin.png" class="col-md-3" id="payment-logo" />
					<div class="col-md-9 text-center text-muted">
						<h6>{l s='For more information, call 888-888-1234' mod='kohortpay'} {l s='or' mod='kohortpay'} <a href="mailto:contact@prestashop.com">contact@prestashop.com</a></h6>
						<a href="#" onclick="javascript:return false;"><i class="icon icon-file"></i> Link to the documentation</a>
						<i class="icon icon-info-circle"></i> {l s='You can also contact us via the contact form on our website.' mod='kohortpay'}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>