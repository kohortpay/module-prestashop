<div class="referral-code-block card block-promo">
  <div class="card-block promo-code text-sm-center">
    {if isset($cashbackAmount)}
      <p>{l s='Cashback unlocked:' mod='kohortpay'} <strong>{$cashbackAmount}</strong></p>
    {else}
      <form method="post" action="{$link->getPageLink('cart', true)}">
        <input type="hidden" name="applyReferralCode" value="1">
        <input type="text" id="referral_code" name="referral_code" class="promo-input" value="{$referral_code|escape:'html':'UTF-8'}" placeholder="{l s='Referral code' mod='kohortpay'}" />
        <button type="submit" class="btn btn-primary">Apply</button>
      </form>
    {/if}
  </div>
</div>
