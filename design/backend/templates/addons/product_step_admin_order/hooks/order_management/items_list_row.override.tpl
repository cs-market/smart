{hook name="order_management:items_list_row"}
<tr>
    <td class="left order-management-product-check">
        <input type="checkbox" name="cart_ids[]" value="{$key}" class="cm-item" /></td>
    <td data-th="{__("product")}">
        <a href="{"products.update?product_id=`$cp.product_id`"|fn_url}">{$cp.product nofilter}</a>
        {include file="views/companies/components/company_name.tpl" object=$cp}
        <a class="cm-confirm cm-post hidden-tools icon-remove-sign order-management-delete" href="{"order_management.delete?cart_ids[]=`$key`"|fn_url}" title="{__("delete")}"></a>
    </td>
    <td data-th="{__("price")}" width="3%" class="order-management-price-check">
        {if $cp.exclude_from_calculate}
            {__("free")}
        {else}
            <input type="hidden" name="cart_products[{$key}][stored_price]" value="N" />
            <input class="inline order-management-price-check-checkbox" type="checkbox" name="cart_products[{$key}][stored_price]" value="Y" {if $cp.stored_price=="Y" }checked="checked" {/if} onchange="Tygh.$('#db_price_{$key},#manual_price_{$key}').toggle();" />
        {/if}
    </td>
    <td class="left order-management-price">
        {if !$cp.exclude_from_calculate}
            {if $cp.stored_price == "Y"}
                {math equation="price - modifier" price=$cp.original_price modifier=$cp.modifiers_price|default:0 assign="original_price"}
            {else}
                {assign var="original_price" value=$cp.original_price}
            {/if}
            <span class="{if $cp.stored_price == "Y"}hidden{/if}" id="db_price_{$key}">{include file="common/price.tpl" value=$original_price}</span>
            <div class="{if $cp.stored_price != "Y"}hidden{/if}" id="manual_price_{$key}">
                {include file="common/price.tpl" value=$cp.base_price view="input" input_name="cart_products[`$key`][price]" class="input-hidden input-mini" product_id=$cp.product_id}
            </div>
        {/if}
    </td>
    {if $cart.use_discount}
        <td data-th="{__("discount")}" class="no-padding nowrap">
            {if $cp.exclude_from_calculate}
                {include file="common/price.tpl" value=""}
            {else}
                {if $cart.order_id}
                    <input type="hidden" name="cart_products[{$key}][stored_discount]" value="Y" />
                    <input type="text" class="input-hidden input-mini cm-numeric" size="5" name="cart_products[{$key}][discount]" value="{$cp.discount}" data-a-sign="{$currencies.$primary_currency.symbol|strip_tags nofilter}" data-a-dec="," data-a-sep="." />
                {else}
                    {include file="common/price.tpl" value=$cp.discount}
                {/if}
            {/if}
        </td>
    {/if}
    <td data-th="{__("quantity")}" class="center order-management-quantity">
        <input type="hidden" name="cart_products[{$key}][product_id]" value="{$cp.product_id}" />
        {if $cp.exclude_from_calculate}
            <input type="hidden" size="3" name="cart_products[{$key}][amount]" value="{$cp.amount}" />
        {/if}
        <span class="cm-reload-{$key}" id="amount_update_{$key}">
            {* <input class="input-hidden input-micro" type="text" size="3" name="cart_products[{$key}][amount]" value="{$cp.amount}" {if $cp.exclude_from_calculate}disabled="disabled" {/if} /> *}
            {if $settings.Appearance.quantity_changer == "Y"}
                <div class="ty-center ty-value-changer cm-value-changer">
                    <a class="cm-increase ty-value-changer__increase">&#43;</a>
            {/if}
            <input type="text" size="3" id="amount_{$key}" name="cart_products[{$key}][amount]" value="{$cp.amount}" data-ca-val="{$cp.amount}" class="input-micro cm-amount" {if $cp.qty_step> 1} data-ca-step="{$cp.qty_step}"{/if} />
            {if $settings.Appearance.quantity_changer == "Y"}
                    <a class="cm-decrease ty-value-changer__decrease">&minus;</a>
                </div>
            {/if}
        <!--amount_update_{$key}--></span>
    </td>
    <td data-th="{__("options")}" width="3%" class="nowrap order-management-options">
        {if $cp.product_options}
            <div id="on_product_options_{$key}_{$cp.product_id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combination-options-{$id}">
                <div class="order-management-options-desktop">
                    <div class="icon-list-ul cm-external-click" data-ca-external-click-id="on_product_options_{$key}_{$cp.product_id}"></div>
                </div>
                <div class="order-management-options-mobile">
                    <div class="btn cm-external-click" data-ca-external-click-id="on_product_options_{$key}_{$cp.product_id}">{__("show_options")}</div>
                </div>
            </div>
            <div id="off_product_options_{$key}_{$cp.product_id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combination-options-{$id}">
                <div class="order-management-options-desktop">
                    <div class="icon-list-ul cm-external-click" data-ca-external-click-id="off_product_options_{$key}_{$cp.product_id}"></div>
                </div>
                <div class="order-management-options-mobile">
                    <div class="btn cm-external-click" data-ca-external-click-id="off_product_options_{$key}_{$cp.product_id}">{__("hide_options")}</div>
                </div>
            </div>
        {/if}
    </td>
</tr>
{if $cp.product_options}
    <tr id="product_options_{$key}_{$cp.product_id}" class="cm-ex-op hidden row-more row-gray order-management-options-content">
        <td class="mobile-hide">&nbsp;</td>
        <td colspan="{if $cart.use_discount}9{else}8{/if}">
            {include file="views/products/components/select_product_options.tpl" product_options=$cp.product_options name="cart_products" id=$key use_exceptions="Y" product=$cp additional_class="option-item"}
            <div id="warning_{$key}" class="pull-left notification-title-e hidden">&nbsp;&nbsp;&nbsp;{__("nocombination")}</div>
        </td>
    </tr>
{/if}
{/hook}
