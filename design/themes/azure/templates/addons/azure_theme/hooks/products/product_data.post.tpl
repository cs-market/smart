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
                    <input {if $product.qty_step > 1}readonly="readonly"{/if} type="text" size="5" class="ty-value-changer__input cm-amount" id="qty_count_{$obj_prefix}{$obj_id}" name="product_data[{$obj_id}][amount]" value="{$default_amount}"{if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} data-ca-min-qty="{if $product.min_qty > 1}{$product.min_qty}{else}1{/if}" />
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