<!--start_ecl_cross_selling_override-->
{capture name="buttons"}
    <div class="ty-float-left">
        {include file="buttons/button.tpl" but_text=__("view_cart") but_meta="ty-btn__secondary" but_href="checkout.cart"}
    </div>
    {if $settings.General.checkout_redirect != "Y"}
        <div class="ty-float-right">
            {include file="buttons/checkout.tpl" but_href="checkout.checkout"}
        </div>
    {/if}
    {$cid = $smarty.session.current_category_id}
    {if $cid}
    <div class="ty-float-right">
	{include file="buttons/button.tpl" but_href="categories.view&amp;category_id=`$cid`" but_text=__("continue_shopping") but_meta="ty-btn__primary"}
    </div>
    {/if}
{/capture}
{capture name="info"}
    <div class="clearfix"></div>
    <hr class="ty-product-notification__divider" />

    <div class="ty-product-notification__total-info clearfix">
        <div class="ty-product-notification__amount ty-float-left"> {__("items_in_cart", [$smarty.session.cart.amount])}</div>
        <div class="ty-product-notification__subtotal ty-float-right">
            {__("cart_subtotal")} {include file="common/price.tpl" value=$smarty.session.cart.display_subtotal}
        </div>
    </div>

    {if $addons.ecl_cross_selling.add_to_cart_notification == 'Y' && !empty($related_products_for_cart)}
    <hr/>
    <div class="related-product-add-cart ty-center">
        <p class="customer-who-bought">{__('customer_who_bought')}</p>
        {include file="addons/ecl_cross_selling/components/products_scroller.tpl" items=$related_products_for_cart block=$block_related_product_data}
    </div>
    {/if}
{/capture}

{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}

<!--end_ecl_cross_selling_override-->