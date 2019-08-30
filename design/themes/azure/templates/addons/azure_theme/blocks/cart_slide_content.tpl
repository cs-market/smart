{assign var="slide_id" value=$block.snapping_id}
{assign var="r_url" value=$config.current_url|escape:url}
<div class="ty-slide {if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} ty-float-right{elseif $content_alignment == "LEFT"} ty-float-left{/if}" id="cart_status_{$slide_id}">
    <div id="sw_slide_{$slide_id}" class="ty-slide__title cm-slide">
        <a href="{"checkout.cart"|fn_url}" class='azure-indicator-container ty-icon-menu'>
            {hook name="checkout:dropdown_title"}
                {if $smarty.session.cart.amount}
                    <i class="ty-azure-icon-cart ty-minicart__icon filled"></i>
                    {if $smarty.session.cart.amount}
                        <div class='azure-count-indication'>{$smarty.session.cart.amount}</div>
                    {/if}
                    <span class="ty-minicart-title ty-hand">{$smarty.session.cart.amount}&nbsp;{__("items")} {__("for")}&nbsp;{include file="common/price.tpl" value=$smarty.session.cart.display_subtotal}</span>
                    <i class="ty-icon-down-micro"></i>
                {else}
                    <i class="ty-azure-icon-cart ty-minicart__icon empty"></i>
                    {if $smarty.session.cart.amount}
                        <div class='azure-count-indication'>{$smarty.session.cart.amount}</div>
                    {/if}
                    <span class="ty-minicart-title empty-cart ty-hand">{__("cart_is_empty")}</span>
                    <i class="ty-icon-down-micro"></i>
                {/if}
            {/hook}
        </a>
    </div>
    <div class="ty-slide__container cm-popup-box" id='slide_{$slide_id}'>
        <div class="ty-slide__content">
            <div class="ty-cart-title">
                <span>{__('cart')}</span>
                <i id="off_slide_{$slide_id}" class="ty-azure-icon-cross cm-slide"></i>
            </div>
            <div class="">
                {hook name="checkout:minicart"}
                    <div class="cm-cart-content {if $block.properties.products_links_type == "thumb"}cm-cart-content-thumb{/if} {if $block.properties.display_delete_icons == "Y"}cm-cart-content-delete{/if}">
                            <div class="ty-cart-items">
                                {if $smarty.session.cart.amount}
                                    <ul class="ty-cart-items__list">
                                        {hook name="index:cart_status"}
                                            {assign var="_cart_products" value=$smarty.session.cart.products|array_reverse:true}
                                            {foreach from=$_cart_products key="key" item="product" name="cart_products"}
                                                {hook name="checkout:minicart_product"}
                                                {if !$product.extra.parent}
                                                    <li class="ty-cart-items__list-item">
                                                        {hook name="checkout:minicart_product_info"}
                                                        {if $block.properties.products_links_type == "thumb"}
                                                            <div class="ty-cart-items__list-item-image">
                                                                {include file="common/image.tpl" image_width="40" image_height="40" images=$product.main_pair no_ids=true}
                                                            </div>
                                                        {/if}
                                                        <div class="ty-cart-items__list-item-desc">
                                                            <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$product.product_id|fn_get_product_name nofilter}</a>
                                                        <p>
                                                            <span>{$product.amount}</span><span>&nbsp;x&nbsp;</span>{include file="common/price.tpl" value=$product.display_price span_id="price_`$key`_`$dropdown_id`" class="none"}
                                                        </p>
                                                        </div>
                                                        {if $block.properties.display_delete_icons == "Y"}
                                                            <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                                                                {if (!$runtime.checkout || $force_items_deletion) && !$product.extra.exclude_from_calculate}
                                                                    {include file="buttons/button.tpl" but_href="checkout.delete.from_status?cart_id=`$key`&redirect_url=`$r_url`" but_meta="cm-ajax cm-ajax-full-render" but_target_id="cart_status*" but_role="delete" but_name="delete_cart_item"}
                                                                {/if}
                                                            </div>
                                                        {/if}
                                                        {/hook}
                                                    </li>
                                                {/if}
                                                {/hook}
                                            {/foreach}
                                        {/hook}
                                    </ul>
                                {else}
                                    <div class="ty-cart-items__empty ty-center">{__("cart_is_empty")}</div>
                                {/if}
                            </div>

                            {if $block.properties.display_bottom_buttons == "Y"}
                            <div class="cm-cart-buttons ty-cart-content__buttons buttons-container{if $smarty.session.cart.amount} full-cart{else} hidden{/if}">
                                <a href="{"checkout.cart"|fn_url}" rel="nofollow" class="ty-btn ty-btn__secondary">{__("view_cart")}</a>
                                {if $settings.General.checkout_redirect != "Y"}
                                    <a href="{"checkout.checkout"|fn_url}" rel="nofollow" class="ty-btn ty-btn__primary">{__("checkout")}</a>
                                {/if}
                            </div>
                            {/if}

                    </div>
                {/hook}
            </div>
        </div>
    </div>
<!--cart_status_{$slide_id}--></div>