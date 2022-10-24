{hook name="checkout:items_list"}

    {if !$cart.products.$key.extra.parent}
        <tr>
            <td class="ty-cart-content__product-elem ty-cart-content__description">
                {if $runtime.mode == "cart" || $show_images}
                    <div class="ty-cart-content__image cm-reload-{$obj_id}" id="product_image_update_{$obj_id}">
                        {hook name="checkout:product_icon"}
                            <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">
                            {include file="common/image.tpl" obj_id=$key images=$product.main_pair image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}</a>
                        {/hook}
                    <!--product_image_update_{$obj_id}--></div>
                {/if}
                <div class="ty-cart-content__description">
                    {strip}
                        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="ty-cart-content__product-title">
                            {$product.product nofilter}
                        </a>
                        
                    {/strip}
                    {hook name="products:product_additional_info"}
                        <div class="ty-cart-content__sku ty-sku cm-hidden-wrapper{if !$product.product_code} hidden{/if}" id="sku_{$key}">
                            {__("sku")}: <span class="cm-reload-{$obj_id}" id="product_code_update_{$obj_id}">{$product.product_code}<!--product_code_update_{$obj_id}--></span>
                        </div>
                        {hook name="checkout:product_options"}
                            {if $product.product_options}
                                <div class="cm-reload-{$obj_id} ty-cart-content__options" id="options_update_{$obj_id}">
                                    <input type="hidden" name="no_cache" value="no_cache" />
                                    {include file="views/products/components/product_options.tpl" product_options=$product.product_options product=$product name="cart_products" id=$key location="cart" disable_ids=$disable_ids form_name="checkout_form"}
                                <!--options_update_{$obj_id}--></div>
                            {/if}
                        {/hook}
                    {/hook}

                    {assign var="name" value="product_options_$key"}
                    {capture name=$name}

                    {capture name="product_info_update"}
                        {hook name="checkout:product_info"}

                        {/hook}
                    {/capture}
                    {if $smarty.capture.product_info_update|trim}
                        <div class="cm-reload-{$obj_id}" id="product_info_update_{$obj_id}">
                            {$smarty.capture.product_info_update nofilter}
                        <!--product_info_update_{$obj_id}--></div>
                    {/if}
                    {/capture}

                    {if $smarty.capture.$name|trim}
                    <div id="options_{$key}" class="ty-product-options ty-group-block">
                        <div class="ty-group-block__arrow">
                            <span class="ty-caret-info"><span class="ty-caret-outer"></span><span class="ty-caret-inner"></span></span>
                        </div>
                        <bdi>{$smarty.capture.$name nofilter}</bdi>
                    </div>
                    {/if}
                </div>
                {if !$product.exclude_from_calculate}
                    <a class="{$ajax_class} ty-cart-content__product-delete ty-delete-big ty-float-right" href="{"checkout.delete?cart_id=`$key`&redirect_mode=`$runtime.mode`"|fn_url}" data-ca-target-id="cart_items,checkout_totals,cart_status*,checkout_steps,checkout_cart" title="{__("remove")}">&nbsp;<i class="ty-delete-big__icon ty-icon-cancel-circle"></i>
                    </a>
                {/if}
            </td>

            <td class="ty-cart-content__product-elem ty-cart-content__price cm-reload-{$obj_id}" id="price_display_update_{$obj_id}">
                {if $product.discount|floatval}
                    <span class="ty-price-old">{include file="common/price.tpl" value=$product.base_price span_id="original_price_`$key`" class=" ty-strike"}</span>
                    <span class="ty-price-actual">{include file="common/price.tpl" value=$product.display_price span_id="product_price_`$key`" class=""}</span>
                    <div>
                        {__('discount')}&nbsp;{include file="common/price.tpl" value=$product.discount span_id="discount_subtotal_`$key`" class="none"}
                    </div>
                {else}
                    {include file="common/price.tpl" value=$product.display_price span_id="product_price_`$key`" class="ty-sub-price"}
                {/if}
            <!--price_display_update_{$obj_id}--></td>

            <td class="ty-cart-content__product-elem ty-cart-content__qty {if $product.is_edp == "Y" || $product.exclude_from_calculate} quantity-disabled{/if}">
                {if $use_ajax == true && $cart.amount != 1}
                    {assign var="ajax_class" value="cm-ajax"}
                {/if}

                <div class="quantity cm-reload-{$obj_id}{if $settings.Appearance.quantity_changer == "Y"} changer{/if}" id="quantity_update_{$obj_id}">
                    <input type="hidden" name="cart_products[{$key}][product_id]" value="{$product.product_id}" />
                    {if $product.exclude_from_calculate}<input type="hidden" name="cart_products[{$key}][extra][exclude_from_calculate]" value="{$product.exclude_from_calculate}" />{/if}

                    <label for="amount_{$key}"></label>
                    {if $product.is_edp == "Y" || $product.exclude_from_calculate}
                        {$product.amount}
                    {else}
                        {if $settings.Appearance.quantity_changer == "Y"}
                            <div class="ty-center ty-value-changer cm-value-changer">
                            <a class="cm-increase ty-value-changer__increase">&#43;</a>
                        {/if}
                        <input type="text" size="3" id="amount_{$key}" name="cart_products[{$key}][amount]" value="{$product.amount}" class="ty-value-changer__input cm-amount"{if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} data-ca-min-qty="{if !$product.min_qty}{$default_minimal_qty}{else}{$product.min_qty}{/if}" />
                        {if $settings.Appearance.quantity_changer == "Y"}
                            <a class="cm-decrease ty-value-changer__decrease">&minus;</a>
                            </div>
                        {/if}
                    {/if}
                    {if $product.is_edp == "Y" || $product.exclude_from_calculate}
                        <input type="hidden" name="cart_products[{$key}][amount]" value="{$product.amount}" />
                    {/if}
                    {if $product.is_edp == "Y"}
                        <input type="hidden" name="cart_products[{$key}][is_edp]" value="Y" />
                    {/if}
                <!--quantity_update_{$obj_id}--></div>
                {if $product.box_contains && $product.box_contains != 1}
                    <div><span id="for_amount_{$key}" data-ca-box-contains="{$product.box_contains}">{($product.amount/$product.box_contains)|round:2}</span>&nbsp;{__('of_box')}</div>
                {/if}
            </td>

            <td class="ty-cart-content__product-elem ty-cart-content__price cm-reload-{$obj_id}" id="price_subtotal_update_{$obj_id}">
                {if $product.discount|floatval}
                    <span class="ty-price-old">{include file="common/price.tpl" value=$product.base_price*$product.amount span_id="original_subtotal_`$key`" class=" ty-strike"}</span>
                    <span class="ty-price-actual">{include file="common/price.tpl" value=$product.display_subtotal span_id="product_subtotal_`$key`" class=""}</span>
                    <div>
                        {__('discount')}&nbsp;{include file="common/price.tpl" value=$product.discount*$product.amount span_id="discount_subtotal_`$key`" class="none"}
                    </div>
                {else}
                    <div class="ty-price-actual">
                        {include file="common/price.tpl" value=$product.display_subtotal span_id="product_subtotal_`$key`" class="price"}
                    </div>
                {/if}
                
                {if $product.zero_price_action == "A"}
                    <input type="hidden" name="cart_products[{$key}][price]" value="{$product.base_price}" />
                {/if}
            <!--price_subtotal_update_{$obj_id}--></td>
        </tr>
    {/if}
{/hook}
