{capture name="package_switcher_`$obj_id`"}
{if $product.items_in_package && $product.items_in_package != 1 && $product.package_switcher == "YesNo::YES"|enum}
    <div class="ty-switcher-checkbox">
        {if isset($product.selected_amount)}
            {assign var="default_amount" value=$product.selected_amount}
        {elseif !empty($product.min_qty)}
            {assign var="default_amount" value=$product.min_qty}
        {elseif !empty($product.qty_step)}
            {assign var="default_amount" value=$product.qty_step}
        {else}
            {assign var="default_amount" value="1"}
        {/if}
        <div class=""><span id="for_qty_count_{$obj_prefix}{$obj_id}" data-ca-box-contains="{$product.items_in_package}">{($default_amount/$product.items_in_package)|round:2}</span>&nbsp;{__('of_box')}</div>

        <div class="ty-switcher-checkbox__controls">
            <input type="checkbox" class="hidden cm-packages-switcher" id="{"switch_checkbox_`$obj_prefix``$obj_id`"}" data-ca-step="{if $product.qty_step}{$product.qty_step}{else}1{/if}" data-ca-qty-input="qty_count_{$obj_prefix}{$obj_id}" name="product_data[{$obj_id}][shop_by_packages]" value="{if $product.items_in_package}{$product.items_in_package}{else}1{/if}">

            {strip}
            <span class="ty-switcher-checkbox__control">
                <input type="radio" id="elm_product_packages_{$obj_prefix}{$obj_id}_0" class="cm-switcher-control" data-ca-state='0' data-ca-target="{"switch_checkbox_`$obj_prefix``$obj_id`"}" name="radio_packages_{$obj_prefix}{$obj_id}" checked="_checked">
                <label class="radio inline" for="elm_product_packages_{$obj_prefix}{$obj_id}_0">{__('product_packages.items')}</label>
            </span>
            <span class="ty-switcher-checkbox__control">
                <input type="radio" id="elm_product_packages_{$obj_prefix}{$obj_id}_1" class="cm-switcher-control" data-ca-state='1' data-ca-target="{"switch_checkbox_`$obj_prefix``$obj_id`"}" name="radio_packages_{$obj_prefix}{$obj_id}">
                <label class="radio inline" for="elm_product_packages_{$obj_prefix}{$obj_id}_1">{__('product_packages.packages')}</label>
            </span>
            {/strip}
        </div>
    </div>
{/if}
{/capture}
{if $no_capture}
    {assign var="capture_name" value="package_switcher_`$obj_id`"}
    {$smarty.capture.$capture_name nofilter}
{/if}
