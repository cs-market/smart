{$_max_desktop_items = $max_desktop_items|default:5}
{$eshop_map_container = "eshop_logistic_map_container_`$shipping.shipping_id`"}
{if $settings.geo_maps.general.provider === "yandex"}
     {$show_move_map_mobile_hint = true}
 {/if}

<div class="ty-checkout-select-store__map-full-div pickup pickup--map-list">

    {* Map *}
    
    <div class="ty-checkout-select-store__map pickup__map-wrapper">
        <div class="pickup__map-container cm-geo-map-container" id="{$eshop_map_container}"
             data-ca-geo-map-initial-lat="{$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval}"
             data-ca-geo-map-initial-lng="{$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval}"
             data-ca-geo-map-zoom="16"
             data-ca-geo-map-controls-enable-zoom="true"
             data-ca-geo-map-controls-enable-fullscreen="true"
             data-ca-geo-map-controls-enable-layers="true"
             data-ca-geo-map-controls-enable-ruler="true"
             data-ca-geo-map-behaviors-enable-drag="true"
             data-ca-geo-map-behaviors-enable-drag-on-mobile="false"
             data-ca-geo-map-behaviors-enable-smart-drag="true"
             data-ca-geo-map-behaviors-enable-dbl-click-zoom="true"
             data-ca-geo-map-behaviors-enable-multi-touch="true"
             data-ca-geo-map-language="{$smarty.const.CART_LANGUAGE}"
             data-ca-geo-map-marker-selector=".cm-eshop-map-marker-{$shipping.shipping_id}"
        ></div>
        {if $show_move_map_mobile_hint}
            <div class="pickup__map-container--mobile-hint">{__("lite_checkout.use_two_fingers_for_move_map")}</div>
        {/if}
    </div>

    {* For mobiles; List wrapper with selected pickup item *}
    {foreach from=$terminals item=store}
        {if !empty($store.lat) && !empty($store.lon)}
            {capture name="marker_content"}
                <div class="litecheckout-ya-baloon">
                    <strong class="litecheckout-ya-baloon__store-name">{$store.address nofilter}</strong>

                    {if $store.note}<p class="litecheckout-ya-baloon__store-address">{$store.note nofilter}</p>{/if}

                    <p class="litecheckout-ya-baloon__select-row">
                        <a data-ca-shipping-id="{$shipping.shipping_id}"
                        data-ca-group-key="{$group_key}"
                        data-ca-location-id="{$store.code}"
                        data-ca-target-map-id="{$eshop_map_container}"
                        class="cm-sl-pickup-select-location ty-btn ty-btn__primary text-button ty-width-full"
                        >{__("select")}</a>
                    </p>

                    {if $store.phone}<p class="litecheckout-ya-baloon__store-phone"><a href="tel:{$store.phone nofilter}">{$store.phone nofilter}</a></p>{/if}
                    {if $store.workTime}<p class="litecheckout-ya-baloon__store-time">{$store.workTime nofilter}</p>{/if}
                </div>
            {/capture}
            
            <div class="cm-eshop-map-marker-{$shipping.shipping_id} hidden"
                data-ca-geo-map-marker-lat="{$store.lat}"
                data-ca-geo-map-marker-lng="{$store.lon}"
                    {if $selected_terminal_id == $store.code || $store_count == 1}
                        data-ca-geo-map-marker-selected="true"
                    {/if}
            >{$smarty.capture.marker_content nofilter}</div>
        {/if}

        {if $selected_terminal_id == $store.code}
        <div class="ty-checkout-select-store pickup__offices-wrapper visible-phone pickup__offices-wrapper--near-map">
            {* List *}
            <div class="litecheckout__fields-row litecheckout__fields-row--wrapped pickup__offices pickup__offices--list pickup__offices--list-no-height">
                {include file="addons/eshop_logistic/views/checkout/components/shippings/items/eshop_logistic.tpl" store=$store}
            </div>
            {* End of List *}
        </div>
        {/if}
    {/foreach}
    {* For mobiles; List wrapper with selected pickup item *}

    {* For mobiles; button for popup with pickup points *}
    <button class="ty-btn ty-btn__secondary cm-open-pickups pickup__open-pickupups-btn visible-phone"
        data-ca-title="{__('lite_checkout.choose_from_list')}"
        data-ca-target=".pickup__offices-wrapper-open"
        type="button"
    >{__('lite_checkout.choose_from_list')}</button>
    <span class="visible-phone cm-open-pickups-msg"></span>
    {* For mobiles; button for popup with pickup points *}

    {* List wrapper *}
    <div class="ty-checkout-select-store pickup__offices-wrapper pickup__offices-wrapper-open hidden-phone">

        {* Search *}
        {if $store_count >= $_max_desktop_items}
        <div class="pickup__search">
            <div class="pickup__search-field litecheckout__field">
                <input type="text"
                       id="pickup-search"
                       class="litecheckout__input js-pickup-search-input"
                       placeholder=" "
                       value=""
                       data-ca-pickup-group-key="{$group_key}"
                />
                <label class="litecheckout__label" for="pickup-search">{__("search")}</label>
            </div>
        </div>
        {/if}
        {* End of Search *}

        {* List *}
        <label for="pickup_office_list"
               class="cm-required cm-multiple-radios hidden"
               data-ca-validator-error-message="{__("pickup_point_not_selected")}"></label>
        <div class="litecheckout__fields-row litecheckout__fields-row--wrapped pickup__offices pickup__offices--list"
             id="pickup_office_list"
             data-ca-error-message-target-node-change-on-screen="xs,xs-large,sm"
             data-ca-error-message-target-node-after-mode="true"
             data-ca-error-message-target-node-on-screen=".cm-open-pickups-msg"
             data-ca-error-message-target-node=".pickup__offices--list"
        >
            {foreach $terminals as $store}
                {include file="addons/eshop_logistic/views/checkout/components/shippings/items/eshop_logistic.tpl" store=$store}
            {/foreach}
        </div>
        {* End of List *}

    </div>
    {* End of List wrapper *}

</div>
