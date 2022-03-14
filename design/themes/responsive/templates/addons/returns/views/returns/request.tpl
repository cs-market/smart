<div class="ty-rma-register">
    <form action="{""|fn_url}" method="post" name="return_registration_form">
    <input name="user_id" type="hidden" value="{$auth.user_id}" />

    {if $actions}
        <div class="ty-rma-register__actions">
            <strong>{__("what_you_would_like_to_do")}:</strong>
            <select class="ty-rma-register__action-select" name="action">
                {foreach from=$actions item="action" key="action_id"}
                    <option value="{$action_id}">{$action.property}</option>
                {/foreach}
            </select>
        </div>
    {/if}

    <table class="ty-table ty-rma-register__table">
    <thead>
        <tr>
            {* <th class="ty-center"><input type="checkbox" name="check_all" value="Y" title="{__("check_uncheck_all")}" class="checkbox cm-check-items" /></th> *}
            <th>{__("product")}</th>
            <th class="ty-right">{__("price")}</th>
            <th>{__("quantity")}</th>
            {* <th>{__("reason")}</th> *}
        </tr>
    </thead>
    <tbody>
    {foreach from=$ordered_products item="product" key="key"}
        <tr>
            {* <td class="ty-center ty-rma-register-id">
                <input type="checkbox" name="returns[{$product.product_id}][chosen]" id="delete_checkbox" value="Y" class="checkbox cm-item" />
                <input type="hidden" name="returns[{$product.product_id}][product_id]" value="{$product.product_id}" />
            </td> *}
            <td style="width: 60%" class="ty-left"><a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
                {if $product.product_options}
                    {include file="common/options_info.tpl" product_options=$product.product_options}
                {/if}
            </td>
            <td class="ty-right ty-nowrap">
                {if $product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$product.price}{/if}
            </td>
            <td class="ty-center">
                <input type="hidden" name="product_data[{$product.product_id}][product_id]" value="{$product.product_id}" />
                {$obj_id = $product.product_id}
                {$show_qty = true}
                {$hide_qty_label = true}
                {$default_amount = 0}
                {$cart_button_exists = true}

                {hook name="products:qty"}
                    <div class="cm-reload-{$obj_prefix}{$obj_id}" id="qty_update_{$obj_prefix}{$obj_id}">
                    <input type="hidden" name="appearance[show_qty]" value="{$show_qty}" />
                    <input type="hidden" name="appearance[capture_options_vs_qty]" value="{$capture_options_vs_qty}" />
                    {if $product.selected_amount !== false}
                        {assign var="default_amount" value=$product.selected_amount}
                    {elseif !empty($product.min_qty)}
                        {assign var="default_amount" value=$product.min_qty}
                    {elseif !empty($product.qty_step)}
                        {assign var="default_amount" value=$product.qty_step}
                    {else}
                        {assign var="default_amount" value="1"}
                    {/if}

                    {if $show_qty && $product.is_edp !== "Y" && ($settings.Checkout.allow_anonymous_shopping == "allow_shopping" || $auth.user_id) && $product.avail_since <= $smarty.const.TIME || ($product.avail_since > $smarty.const.TIME && $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum)}
                        <div class="ty-qty clearfix{if $settings.Appearance.quantity_changer == "Y"} changer{/if}" id="qty_{$obj_prefix}{$obj_id}">
                            {if !$hide_qty_label}<label class="ty-control-group__label" for="qty_count_{$obj_prefix}{$obj_id}">{$quantity_text|default:__("quantity")}:</label>{/if}
                            {if $product.qty_content}
                            <select name="product_data[{$obj_id}][amount]" id="qty_count_{$obj_prefix}{$obj_id}">
                                {assign var="a_name" value="product_amount_`$obj_prefix``$obj_id`"}
                                {assign var="selected_amount" value=false}
                                {foreach name="`$a_name`" from=$product.qty_content item="var"}
                                    <option value="{$var}" {if $product.selected_amount && ($product.selected_amount == $var || ($smarty.foreach.$a_name.last && !$selected_amount))}{assign var="selected_amount" value=true}selected="selected"{/if}>{$var}</option>
                                {/foreach}
                            </select>
                            {else}
                            <div class="ty-center ty-value-changer cm-value-changer">
                                {if $settings.Appearance.quantity_changer == "Y"}
                                    <a class="cm-increase ty-value-changer__increase">&#43;</a>
                                {/if}

                                <input {if $product.qty_step > 1}readonly="readonly"{/if} type="text" size="5" class="ty-value-changer__input cm-amount" id="qty_count_{$obj_prefix}{$obj_id}" name="product_data[{$obj_id}][amount]" value="{$default_amount}"{if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} data-ca-min-qty="{if $product.min_qty > 1}{$product.min_qty}{else}1{/if}" />
                                {if $settings.Appearance.quantity_changer == "Y"}
                                    <a class="cm-decrease ty-value-changer__decrease">&minus;</a>
                                {/if}
                            </div>
                            {/if}
                        </div>
                        {if $product.prices}
                            {include file="views/products/components/products_qty_discounts.tpl"}
                        {/if}
                    {elseif !$bulk_add}
                        <input type="hidden" name="product_data[{$obj_id}][amount]" value="{$default_amount}" />
                    {/if}
                    <!--qty_update_{$obj_prefix}{$obj_id}--></div>
                {/hook}
            </td>
            {* <td class="ty-center">
                {if $reasons}
                    <select name="returns[{$product.product_id}][reason]">
                    {foreach from=$reasons item="reason" key="reason_id"}
                        <option value="{$reason_id}">{$reason.property}</option>
                    {/foreach}
                    </select>
                {/if}
            </td> *}
        </tr>
    {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="6"><p class="ty-no-items">{__("no_items")}</p></td>
        </tr>
    {/foreach}
    </tbody>
    </table>

    {* <div class="ty-rma-register__comments">
        <strong class="ty-rma-register__comments-title">{__("type_comment")}</strong>
        <textarea name="comment" cols="3" rows="4" class="ty-rma-register__comments-textarea"></textarea>
    </div> *}
    <div class="ty-rma-register__buttons buttons-container">
        {include file="buttons/button.tpl" but_text=__("request_return") but_name="dispatch[returns.add_return]" but_meta="ty-btn__secondary cm-process-items"}
    </div>

    </form>
</div>

{capture name="mainbox_title"}{__("request_return")}{/capture}
