{**
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
 *}

<div class="panel">
	<div class="row kohortpay-header">
		<img src="{$module_dir|escape:'html':'UTF-8'}views/img/kohortpay_logo_admin.png" class="col-xs-12 col-md-2 text-center" id="payment-logo" />
		<div class="col-xs-12 col-md-7 text-center text-muted" style="padding: 15px 80px">
			{l s='KohortPay lets your customers pay, refer and save on every purchase. Cut your customer acquisition costs in half while offering your customers a social and fun brand experience. And just like that, your checkout becomes so koool.' mod='kohortpay'}
		</div>
		<div class="col-xs-12 col-md-3 text-center" style="padding-top: 10px">
			<a href="https://dashboard.kohortpay.com/sign-up" target="_blank" class="btn btn-primary" id="create-account-btn">{l s='Create an account' mod='kohortpay'}</a><br />
			{l s='Already have one?' mod='kohortpay'}<a href="https://dashboard.kohortpay.com/sign-in" target="_blank"> {l s='Log in' mod='kohortpay'}</a>
		</div>
	</div>

	<hr />
	
	<div class="kohortpay-content">
		<div class="row">
			<div class="col-md-5">
				<h2 style="padding-bottom: 10px">{l s='Benefits' mod='kohortpay'}</h5>
				<ul class="ul-spaced">
					<li>
						<strong>{l s='No setup costs' mod='kohortpay'}:</strong>
						{l s='Integrate KohortPay on your site and increase customer satisfaction in 10 minutes.' mod='kohortpay'}
					</li>
					
					<li>
						<strong>{l s='Lower acquisition costs' mod='kohortpay'}:</strong>
						{l s='Drive high-quality customer acquisition at half the cost of existing channels.' mod='kohortpay'}
					</li>
					
					<li>
						<strong>{l s='Pay for performance' mod='kohortpay'}:</strong>
						{l s='No commitments. You only pay for results. Start and stop in 1 click.' mod='kohortpay'}
					</li>
					
					<li>
						<strong>{l s='Brand reinforcement' mod='kohortpay'}:</strong>
						{l s='Harness the power of  word-of-mouth recommendations.' mod='kohortpay'}
					</li>
				</ul>
			</div>
			
			<div class="col-md-2">
				<h2 style="padding-bottom: 10px">{l s='Pricing' mod='kohortpay'}</h2>
				<dl class="list-unstyled">
					<dt>{l s='Payment Fees' mod='kohortpay'}</dt>
					<dd>0,25€ + 2%</dd>
					<dt style="padding-top: 8px">{l s='Acquisition Fees' mod='kohortpay'}</dt>
					<dd>10%</dd>
				</dl>
			</div>
			
			<div class="col-md-5">
				<h2 style="padding-bottom: 10px">{l s='How does it work?' mod='kohortpay'}</h2>
				<img src="{$module_dir|escape:'html':'UTF-8'}views/img/kohortpay_how_admin.png" width="400px" />
			</div>
		</div>

		<hr />
		
		<div class="row">
			<div class="col-md-12">				
				<div class="row">
					<img src="{$module_dir|escape:'html':'UTF-8'}views/img/kohortpay_cards_admin.png" class="col-md-3" id="payment-logo" />
					<div class="col-md-9 text-center text-muted" style="padding-top:10px">
						{l s="If you any questions, don't hesitate to read " mod='kohortpay'}
						<a href="https://docs.kohortpay.com/" target="_blank" >{l s='our documentation' mod='kohortpay'}</a>.
						 {l s='You can also contact us by email:' mod='kohortpay'}
						 <a href="mailto:support@kohortpay.com">support@kohortpay.com</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>