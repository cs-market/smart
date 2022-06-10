{if $product_groups|count > 1}

{assign var="result_ids" value="cart*,checkout*"}

<form name="checkout_form" class="cm-check-changes cm-ajax cm-ajax-full-render" action="{""|fn_url}" method="post" enctype="multipart/form-data" id="checkout_form">
<input type="hidden" name="redirect_mode" value="cart" />
<input type="hidden" name="result_ids" value="{$result_ids}" />

<h1 class="ty-mainbox-title">{__("cart_contents")}</h1>

<div class="buttons-container ty-cart-content__top-buttons clearfix">
    <div class="ty-float-left ty-cart-content__left-buttons">
        {hook name="checkout:cart_content_top_left_buttons"}
            {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url }
        {/hook}
    </div>
    <div class="ty-float-right ty-cart-content__right-buttons">
        {hook name="checkout:cart_content_top_right_buttons"}
            {include file="buttons/update_cart.tpl"
                     but_id="button_cart"
                     but_meta="ty-btn--recalculate-cart hidden hidden-phone hidden-tablet"
                     but_name="dispatch[checkout.update]"
            }
            {if $payment_methods}
                {include file="buttons/proceed_to_checkout.tpl"}
            {/if}
        {/hook}
    </div>
</div>

{foreach $product_groups as $group}
    <h3>{$group.name}</h3>
    {include file="views/checkout/components/cart_items.tpl" disable_ids="button_cart" cart_products=$group['products']}
    <div class="ty-cart-total">
        <div class="ty-cart-total__wrapper clearfix" id="checkout_totals">        
            {hook name="checkout:payment_options"}
            {/hook}
            
            {include file="views/checkout/components/checkout_totals_info.tpl" cart=$group}

            <div class="clearfix"></div>
        <!--checkout_totals--></div>
    </div>

    {*include file="views/checkout/components/checkout_totals.tpl" location="cart" cart=$group*}
{/foreach}
<!--checkout_form--></form>

<div class="ty-cart-total">
    <div class="ty-cart-total__wrapper clearfix" id="checkout_totals">
        {if $cart_products}
            <div class="ty-coupons__container">
                {include file="views/checkout/components/promotion_coupon.tpl"}
                {hook name="checkout:payment_extra"}
                {/hook}
            </div>
        {/if}
        
        {hook name="checkout:payment_options"}
        {/hook}
        
        <div class="clearfix"></div>
        <ul class="ty-cart-statistic__total-list">
            <li class="ty-cart-statistic__item ty-cart-statistic__total">
                <span class="ty-cart-statistic__total-title">{__("total_cost")}</span>
                <span class="ty-cart-statistic__total-value">
                    {include file="common/price.tpl" value=$_total|default:$smarty.capture._total|default:$cart.total span_id="cart_total" class="ty-price"}
                </span>
            </li>
        </ul>
    <!--checkout_totals--></div>
</div>

<div class="buttons-container ty-cart-content__bottom-buttons clearfix">
    <div class="ty-float-left ty-cart-content__left-buttons">
        {hook name="checkout:cart_content_bottom_left_buttons"}
            {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url}
            {include file="buttons/clear_cart.tpl" but_href="checkout.clear" but_role="text" but_meta="cm-confirm ty-cart-content__clear-button"}
        {/hook}
    </div>
    <div class="ty-float-right ty-cart-content__right-buttons">
        {hook name="checkout:cart_content_bottom_right_buttons"}
            {if $payment_methods}
                {assign var="link_href" value="checkout.checkout"}
                {include file="buttons/proceed_to_checkout.tpl"}
            {/if}
        {/hook}
    </div>
</div>
{if $checkout_add_buttons}
    <div class="ty-cart-content__payment-methods payment-methods" id="payment-methods">
        <span class="ty-cart-content__payment-methods-title payment-metgods-or">{__("or_use")}</span>
        <table class="ty-cart-content__payment-methods-block">
            <tr>
                {foreach from=$checkout_add_buttons item="checkout_add_button"}
                    <td class="ty-cart-content__payment-methods-item">{$checkout_add_button nofilter}</td>
                {/foreach}
            </tr>
    </table>
    <!--payment-methods--></div>
{/if}
{else}
    {include file="views/../views/checkout/components/cart_content.tpl"}
{/if}
