{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'sdek' && $shipping.data.offices|count >= 1}
    {assign var="office_count" value=$shipping.data.offices|count}
    {assign var="shipping_id" value=$shipping.shipping_id}
    {assign var="old_office_id" value=$select_office.$group_key.$shipping_id}
    {$sdek_map_container = "sdek_map_$shipping_id"}
    {include file="addons/rus_sdek/views/sdek/yandex.tpl"}

    <div class="ty-sdek-select-store__map-wrapper">
        <div class="ty-sdek-select-store__map" id="{$sdek_map_container}">
        </div>
    </div>
    <div class="ty-sdek-office-search ty-sdek-office-search-disabled">
        <input id="sdek_office_search" type="text" title="{__("addons.rus_sdek.search_string")}" class="ty-input-text-medium cm-hint ty-search-office">
    </div>

    {include file="addons/rus_sdek/views/sdek/sdek_offices.tpl" group_key=$group_key shipping_id=$shipping.shipping_id sdek_offices=$shipping.data.sdek_offices}

    {if $office_count > 6}
        <div class="ty-mtb-s ty-uppercase clearfix">
            <a class="cm-show-all-point cm-ajax" href="{"sdek.sdek_offices?group_key=`$group_key`&shipping_id=`$shipping_id`&old_office_id=`$old_office_id`"|fn_url}" id="sdek_show_all" data-ca-scroll={$sdek_map_container} data-ca-group-key={$group_key} data-ca-shipping-id={$shipping_id}>{__("addons.rus_sdek.show_all")}</a>
        </div>
    {/if}
{/if}
