{assign var="result_ids" value="cart*,checkout*"}

<div id="checkout_form_wrapper">
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

{include file="views/checkout/components/cart_items.tpl" disable_ids="button_cart"}

</form>
<!--checkout_form_wrapper--></div>
