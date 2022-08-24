{if $product.box_contains && $product.box_contains != 1 && $product.package_switcher == "YesNo::YES"|enum}

    {if isset($product.selected_amount)}
        {assign var="default_amount" value=$product.selected_amount}
    {elseif !empty($product.min_qty)}
        {assign var="default_amount" value=$product.min_qty}
    {elseif !empty($product.qty_step)}
        {assign var="default_amount" value=$product.qty_step}
    {else}
        {assign var="default_amount" value="1"}
    {/if}
    <div class="ty-left"><span id="for_qty_count_{$obj_id}" data-ca-box-contains="{$product.box_contains}">{($default_amount/$product.box_contains)|round:2}</span>&nbsp;{__('of_box')}</div>

    <div class="ty-switcher-checkbox">
        {* {include file="buttons/switch_button.tpl"
            id="switch_button_`$obj_id`"
            class="cm-switcher-button ty-left"
            data_on=__('product_packages.items')
            data_off=__('product_packages.packages')
            additional_attrs=['data-ca-step' => $product.qty_step, 'data-ca-items-in-package' => $product.items_in_package ]
        } *}
        <input type="checkbox" class="hidden cm-packages-switcher" id="{"switch_checkbox_`$obj_id`"}" data-ca-step="{$product.qty_step}" data-ca-qty-input="qty_count_{$obj_id}" name="product_data[{$obj_id}][shop_by_packages]" value="{$product.items_in_package}">
        {strip}
        <span class="ty-switcher-checkbox__control cm-switcher-control" data-ca-state='0' data-ca-target="{"switch_checkbox_`$obj_id`"}">{__('product_packages.items')}</span>
        <span class="ty-switcher-checkbox__control cm-switcher-control" data-ca-state='1' data-ca-target="{"switch_checkbox_`$obj_id`"}">{__('product_packages.packages')}</span>
        {/strip}
    </div>
{/if}
