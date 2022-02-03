{if $product.is_weighted == "YesNo::YES"|enum}
    <div class="cm-reload-{$obj_prefix}{$obj_id}" id="qty_update_{$obj_prefix}{$obj_id}">
    <input type="hidden" name="appearance[show_qty]" value="{$show_qty}" />
    <input type="hidden" name="appearance[capture_options_vs_qty]" value="{$capture_options_vs_qty}" />
    {if (!empty($product.selected_amount) || $product.selected_amount == '0')}
        {assign var="default_amount" value=$product.selected_amount}
    {elseif !empty($product.min_qty)}
        {assign var="default_amount" value=$product.min_qty}
    {elseif !empty($product.qty_step)}
        {assign var="default_amount" value=$product.qty_step}
    {else}
        {assign var="default_amount" value="1"}
    {/if}

    {if $show_qty && $product.is_edp !== "Y" && ($settings.Checkout.allow_anonymous_shopping == "allow_shopping" || $auth.user_id) && $product.avail_since <= $smarty.const.TIME || ($product.avail_since > $smarty.const.TIME && $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum)}
        <div class="ty-qty clearfix " id="qty_{$obj_prefix}{$obj_id}">
            {if !$hide_qty_label}<label class="ty-control-group__label" for="qty_count_{$obj_prefix}{$obj_id}">{$quantity_text|default:__("quantity")}:</label>{/if}
            <div class="ty-center ty-value-changer cm-value-changer ty-weighed">
                <input type="text" size="5" class="ty-value-changer__input" id="qty_count_{$obj_prefix}{$obj_id}" name="product_data[{$obj_id}][amount_int]" value="{$default_amount|intval}" />
                {__('kilo')}
                {$default_amount_dec = ($default_amount - $default_amount|floor) * 1000}
                <input  type="text" size="5" class="ty-value-changer__input" id="qty_count_dec_{$obj_prefix}{$obj_id}" name="product_data[{$obj_id}][amount_dec]" value="{$default_amount_dec}"/>
                {__('gram')}
            </div>
        </div>
        {if $product.prices}
            {include file="views/products/components/products_qty_discounts.tpl"}
        {/if}
    {elseif !$bulk_add}
        <input type="hidden" name="product_data[{$obj_id}][amount]" value="{$default_amount}" />
    {/if}
    <!--qty_update_{$obj_prefix}{$obj_id}--></div>
{/if}
