{********************** Old Price *****************}
{capture name="old_price_`$obj_id`"}
    {if $show_price_values && $show_old_price && ($product.discount || $product.list_discount)}
        <div class="cm-reload-{$obj_prefix}{$obj_id} ty-price-old" id="old_price_update_{$obj_prefix}{$obj_id}">
            {hook name="products:old_price"}
            {if $product.discount}
                <span class="ty-strike">{include file="common/price.tpl" value=$product.original_price|default:$product.base_price span_id="old_price_`$obj_prefix``$obj_id`" class="ty-nowrap"}</span>
            {elseif $product.list_discount}
                <span class="ty-strike">{include file="common/price.tpl" value=$product.list_price span_id="list_price_`$obj_prefix``$obj_id`" class="ty-nowrap"}</span>
            {/if}
            {/hook}
        <!--old_price_update_{$obj_prefix}{$obj_id}--></div>
    {/if}
{/capture}
{if $no_capture}
    {assign var="capture_name" value="old_price_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}

{********************** Price *********************}
{capture name="price_`$obj_id`"}
    <div class="{if $product.zero_price_action !== "A"}cm-reload-{$obj_prefix}{$obj_id}{/if} ty-price-update ty-price-actual" id="price_update_{$obj_prefix}{$obj_id}">
        <input type="hidden" name="appearance[show_price_values]" value="{$show_price_values}" />
        <input type="hidden" name="appearance[show_price]" value="{$show_price}" />
        {if $show_price_values}
            {if $show_price}
            {hook name="products:prices_block"}
                {if $product.price|floatval || $product.zero_price_action == "P" || ($hide_add_to_cart_button == "Y" && $product.zero_price_action == "A")}
                    <span class="ty-price{if !$product.price|floatval && !$product.zero_price_action} hidden{/if}" id="line_discounted_price_{$obj_prefix}{$obj_id}">{include file="common/price.tpl" value=$product.price span_id="discounted_price_`$obj_prefix``$obj_id`" class="" live_editor_name="product:price:{$product.product_id}" live_editor_phrase=$product.base_price}</span>
                {elseif $product.zero_price_action == "A" && $show_add_to_cart}
                    {assign var="base_currency" value=$currencies[$smarty.const.CART_PRIMARY_CURRENCY]}
                    <span class="ty-price-curency"><span class="ty-price-curency__title">{__("enter_your_price")}:</span>
                    <div class="ty-price-curency-input">
                        <input 
                            type="text"
                            name="product_data[{$obj_id}][price]"
                            class="ty-price-curency__input cm-numeric"
                            data-a-sign="{$base_currency.symbol nofilter}" 
                            data-a-dec="{if $base_currency.decimal_separator}{$base_currency.decimal_separator nofilter}{else}.{/if}" 
                            data-a-sep="{if $base_currency.thousands_separator}{$base_currency.thousands_separator nofilter}{else},{/if}"
                            data-p-sign="{if $base_currency.after === "YesNo::YES"|enum}s{else}p{/if}"
                            data-m-dec="{$base_currency.decimals}"
                            size="3"
                            value=""
                        />
                    </div>
                    </span>

                {elseif $product.zero_price_action == "R"}
                    <span class="ty-no-price">{__("contact_us_for_price")}</span>
                    {assign var="show_qty" value=false}
                {/if}
            {/hook}
            {/if}
        {elseif $settings.Checkout.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id}
            <span class="ty-price">{__("sign_in_to_view_price")}</span>
        {/if}
    <!--price_update_{$obj_prefix}{$obj_id}--></div>
{/capture}
{if $no_capture}
    {assign var="capture_name" value="price_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}

{************************************ Discount label ****************************}
{capture name="discount_label_`$obj_prefix``$obj_id`"}
    {if $show_discount_label && ($product.discount_prc || $product.list_discount_prc) && $show_price_values}
        <span class="ty-discount-label cm-reload-{$obj_prefix}{$obj_id}" id="discount_label_update_{$obj_prefix}{$obj_id}">
            <span class="ty-discount-label__item" id="line_prc_discount_value_{$obj_prefix}{$obj_id}"><span class="ty-discount-label__value" id="prc_discount_value_label_{$obj_prefix}{$obj_id}">-{if $product.discount}{$product.discount_prc}{else}{$product.list_discount_prc}{/if}%</span></span>
        <!--discount_label_update_{$obj_prefix}{$obj_id}--></span>
    {/if}
{/capture}
{if $no_capture}
    {assign var="capture_name" value="discount_label_`$obj_prefix``$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}

{capture name="qty_`$obj_id`"}
    {hook name="products:qty"}
        <div class="cm-reload-{$obj_prefix}{$obj_id}" id="qty_update_{$obj_prefix}{$obj_id}">
        <input type="hidden" name="appearance[show_qty]" value="{$show_qty}" />
        <input type="hidden" name="appearance[capture_options_vs_qty]" value="{$capture_options_vs_qty}" />
        {if !empty($product.selected_amount)}
            {assign var="default_amount" value=$product.selected_amount}
        {elseif !empty($product.min_qty)}
            {assign var="default_amount" value=$product.min_qty}
        {elseif !empty($product.qty_step)}
            {assign var="default_amount" value=$product.qty_step}
        {else}
            {assign var="default_amount" value="1"}
        {/if}

        {if $show_qty && $product.is_edp !== "Y" && $cart_button_exists == true && ($settings.General.allow_anonymous_shopping == "allow_shopping" || $auth.user_id) && $product.avail_since <= $smarty.const.TIME || ($product.avail_since > $smarty.const.TIME && $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum)}
            <div class="ty-qty clearfix{if $settings.Appearance.quantity_changer == "Y"} changer{/if}" id="qty_{$obj_prefix}{$obj_id}">
                {if !$hide_qty_label}<label class="ty-control-group__label" for="qty_count_{$obj_prefix}{$obj_id}">{$quantity_text|default:__("quantity")}:</label>{/if}
                <div class="ty-center ty-value-changer cm-value-changer">
                    {if $settings.Appearance.quantity_changer == "Y"}
                        <a class="cm-increase ty-value-changer__increase">&#43;</a>
                    {/if}
                    <input type="text" size="5" class="ty-value-changer__input cm-amount" id="qty_count_{$obj_prefix}{$obj_id}" name="product_data[{$obj_id}][amount]" value="{$default_amount}"{if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} data-ca-min-qty="{if $product.min_qty > 1}{$product.min_qty}{else}1{/if}" />
                    {if $settings.Appearance.quantity_changer == "Y"}
                        <a class="cm-decrease ty-value-changer__decrease">&minus;</a>
                    {/if}
                </div>
            </div>
            {if $product.prices}
                {include file="views/products/components/products_qty_discounts.tpl"}
            {/if}
        {elseif !$bulk_add}
            <input type="hidden" name="product_data[{$obj_id}][amount]" value="{$default_amount}" />
        {/if}
        <!--qty_update_{$obj_prefix}{$obj_id}--></div>
    {/hook}
{/capture}
{if $no_capture}
    {assign var="capture_name" value="qty_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}

{capture name="form_open_`$obj_id`"}
{if !$hide_form}
<form action="{""|fn_url}" method="post" name="product_form_{$obj_prefix}{$obj_id}" enctype="multipart/form-data" class="cm-disable-empty-files {if $is_ajax} cm-ajax cm-ajax-full-render cm-ajax-status-middle{/if} {if $form_meta}{$form_meta}{/if}">
<input type="hidden" name="result_ids" value="cart_status*,wish_list*,checkout*,account_info*,add_to_cart_update*" />
{if !$stay_in_cart}
<input type="hidden" name="redirect_url" value="{$redirect_url|default:$config.current_url}" />
{/if}
<input type="hidden" name="product_data[{$obj_id}][product_id]" value="{$product.product_id}" />
{/if}
{/capture}
{if $no_capture}
    {assign var="capture_name" value="form_open_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}

{capture name="add_to_cart_`$obj_id`"}
{if $show_add_to_cart}
<div class="cm-reload-{$obj_prefix}{$obj_id} {$add_to_cart_class}" id="add_to_cart_update_{$obj_prefix}{$obj_id}">
<input type="hidden" name="appearance[show_add_to_cart]" value="{$show_add_to_cart}" />
<input type="hidden" name="appearance[show_list_buttons]" value="{$show_list_buttons}" />
<input type="hidden" name="appearance[but_role]" value="{$but_role}" />
<input type="hidden" name="appearance[quick_view]" value="{$quick_view}" />

{strip}
{capture name="buttons_product"}
    {hook name="products:add_to_cart"}
        {if $product.has_options && !$show_product_options && !$details_page}
            {if $but_role == "text"}
                {$opt_but_role="text"}
            {else}
                {$opt_but_role="action"}
            {/if}

            {include file="buttons/button.tpl" but_id="button_cart_`$obj_prefix``$obj_id`" but_text=__("select_options") but_href="products.view?product_id=`$product.product_id`" but_role=$opt_but_role but_name="" but_meta="ty-btn__primary ty-btn__big"}
        {else}
            {hook name="products:add_to_cart_but_id"}
                {$_but_id="button_cart_`$obj_prefix``$obj_id`"}
            {/hook}

            {if $extra_button}{$extra_button nofilter}&nbsp;{/if}
            {include file="buttons/add_to_cart.tpl" but_id=$_but_id but_name="dispatch[checkout.add..`$obj_id`]" but_role=$but_role block_width=$block_width obj_id=$obj_id product=$product but_meta=$add_to_cart_meta}

            {assign var="cart_button_exists" value=true}
        {/if}
    {/hook}
{/capture}
{hook name="products:buttons_block"}
    {if (
            $product.zero_price_action != "R"
            || $product.price != 0
        )
        && (
            $settings.General.inventory_tracking == "YesNo::NO"|enum
            || $settings.General.allow_negative_amount == "Y"
            || (
                $product_amount > 0
                && $product_amount >= $product.min_qty
            )
            || $product.tracking == "ProductTracking::DO_NOT_TRACK"|enum
            || $product.is_edp == "Y"
            || $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum
        )
        || (
            $product.has_options
            && !$show_product_options
        )}

        {if $smarty.capture.buttons_product|trim != '&nbsp;'}
            {if $product.avail_since <= $smarty.const.TIME || (
                $product.avail_since > $smarty.const.TIME && $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum
            )}
                {$smarty.capture.buttons_product nofilter}
            {/if}
        {/if}

    {elseif ($settings.General.inventory_tracking !== "YesNo::NO"|enum && $settings.General.allow_negative_amount != "Y" && (($product_amount <= 0 || $product_amount < $product.min_qty) && $product.tracking != "ProductTracking::DO_NOT_TRACK"|enum) && $product.is_edp != "Y")}
        {hook name="products:out_of_stock_block"}
            {assign var="show_qty" value=false}
            {if !$details_page}
                {if (!$product.hide_stock_info && !(($product_amount <= 0 || $product_amount < $product.min_qty) && ($product.avail_since > $smarty.const.TIME)))}
                    <span class="ty-qty-out-of-stock ty-control-group__item" id="out_of_stock_info_{$obj_prefix}{$obj_id}">{$out_of_stock_text}</span>
                {/if}
            {elseif ($product.out_of_stock_actions == "OutOfStockActions::SUBSCRIBE"|enum)}
                {hook name="product_data:back_in_stock_checkbox"}
                <div class="ty-control-group">
                    <label for="sw_product_notify_{$obj_prefix}{$obj_id}" class="ty-strong" id="label_sw_product_notify_{$obj_prefix}{$obj_id}">
                        <input id="sw_product_notify_{$obj_prefix}{$obj_id}" type="checkbox" class="checkbox cm-switch-availability cm-switch-visibility" name="product_notify" {if $product_notification_enabled == "Y"}checked="checked"{/if} onclick="
                            {if !$auth.user_id}
                                if (!this.checked) {
                                    Tygh.$.ceAjax('request', '{"products.product_notifications?enable="|fn_url}' + 'N&amp;product_id={$product.product_id}&amp;email=' + $('#product_notify_email_{$obj_prefix}{$obj_id}').get(0).value, {$ldelim}cache: false{$rdelim});
                                }
                            {else}
                                Tygh.$.ceAjax('request', '{"products.product_notifications?enable="|fn_url}' + (this.checked ? 'Y' : 'N') + '&amp;product_id=' + '{$product.product_id}', {$ldelim}cache: false{$rdelim});
                            {/if}
                        "/>{__("notify_when_back_in_stock")}
                    </label>
                </div>
                {/hook}
                {if !$auth.user_id }
                <div class="ty-control-group ty-input-append ty-product-notify-email {if $product_notification_enabled != "Y"}hidden{/if}" id="product_notify_{$obj_prefix}{$obj_id}">

                    <input type="hidden" name="enable" value="Y" disabled />
                    <input type="hidden" name="product_id" value="{$product.product_id}" disabled />

                    <label id="product_notify_email_label" for="product_notify_email_{$obj_prefix}{$obj_id}" class="cm-required cm-email hidden">{__("email")}</label>
                    <input type="text" name="email" id="product_notify_email_{$obj_prefix}{$obj_id}" size="20" value="{$product_notification_email|default:__("enter_email")}" class="ty-product-notify-email__input cm-hint" title="{__("enter_email")}" disabled />

                    <button class="ty-btn-go cm-ajax" type="submit" name="dispatch[products.product_notifications]" title="{__("go")}"><i class="ty-btn-go__icon ty-icon-right-dir"></i></button>

                </div>
                {/if}
            {/if}
        {/hook}
    {/if}

    {if $show_list_buttons}
        {capture name="product_buy_now_`$obj_id`"}
            {$compare_product_id = $product.product_id}

            {hook name="products:buy_now"}
            {if $settings.General.enable_compare_products == "Y"}
                {include file="buttons/add_to_compare_list.tpl" product_id=$compare_product_id}
            {/if}
            {/hook}
        {/capture}
        {assign var="capture_buy_now" value="product_buy_now_`$obj_id`"}

        {if $smarty.capture.$capture_buy_now|trim}
            {$smarty.capture.$capture_buy_now nofilter}
        {/if}
    {/if}

    {if ($product.avail_since > $smarty.const.TIME)}
        {include file="common/coming_soon_notice.tpl" avail_date=$product.avail_since add_to_cart=$product.out_of_stock_actions}
    {/if}

    {* Uncomment these lines in the overrides hooks for back-passing $cart_button_exists variable to the product_data template *}
    {*if $cart_button_exists}
        {capture name="cart_button_exists"}Y{/capture}
    {/if*}
{/hook}
{/strip}
{*<!--add_to_cart_update_{$obj_prefix}{$obj_id}-->*}</div>
{/if}
{/capture}
