{$disable_funding = $payment_method.processor_params.disable_funding|default:[]}
{$disable_card = $payment_method.processor_params.disable_card|default:[]}
{if $disable_funding.card === "card"}
    {$disable_card = []}
{/if}
<input type="hidden"
       data-ca-paypal-commerce-platform-checkout="true"
       data-ca-paypal-commerce-platform-button="litecheckout_place_order"

       data-ca-paypal-commerce-platform-client-id="{$payment_method.processor_params.client_id}"
       data-ca-paypal-commerce-platform-bn-code="{$payment_method.processor_params.bn_code}"
       data-ca-paypal-commerce-platform-currency="{$payment_method.processor_params.currency}"
       data-ca-paypal-commerce-platform-disable-funding="{$disable_funding|array_filter|implode:","}"
       data-ca-paypal-commerce-platform-disable-card="{$disable_card|array_filter|implode:","}"
       data-ca-paypal-commerce-platform-merchant-ids="{$payment_method.processor_params.merchant_ids|default:[]|array_filter|implode:","}"
       data-ca-paypal-commerce-platform-style-layout="{$payment_method.processor_params.style.layout|default:"vertical"}"
       data-ca-paypal-commerce-platform-style-color="{$payment_method.processor_params.style.color|default:"gold"}"
       data-ca-paypal-commerce-platform-style-height="{$payment_method.processor_params.style.height|default:55}"
       data-ca-paypal-commerce-platform-style-shape="{$payment_method.processor_params.style.shape|default:"rect"}"
       data-ca-paypal-commerce-platform-style-label="{$payment_method.processor_params.style.label|default:"pay"}"
       data-ca-paypal-commerce-platform-style-tagline="{$payment_method.processor_params.style.tagline|default:"false"}"

       data-ca-paypal-commerce-platform-debug="{if $payment_method.processor_params.mode === "live"}false{else}true{/if}"
/>
