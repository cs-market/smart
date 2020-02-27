{script src="js/addons/rus_sdek/yandex.js"}

<script type="text/javascript" class="cm-ajax-force">
    (function(_, $) {
        var map_options = [];
        map_options[{$shipping.shipping_id}] = {
            'latitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval},
            'longitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval},
            'sdek_map_container': '{$sdek_map_container}',
            'group_key': {$group_key},
            'shipping_id' : '{$shipping.shipping_id}',
            'zoom': {if !empty($sl_settings.yandex_zoom)} {$sl_settings.yandex_zoom} {else} 16 {/if},
            'controls': [
                'zoomControl',
                'typeSelector',
                'rulerControl',
            ],
            'language': '{$smarty.const.CART_LANGUAGE}',
            'selectStore': true,
            'storeData': [
                {foreach from=$shipping.data.offices item="sdek_office" name="st_loc_foreach" key="key"}
                {
                    'store_location_id' : '{$sdek_office.Code}',
                    'group_key' : '{$group_key}',
                    'shipping_id' : '{$shipping.shipping_id}',
                    'latitude' : {$sdek_office.coordY|doubleval},
                    'longitude' : {$sdek_office.coordX|doubleval},
                    'name' :  '{$sdek_office.Name|escape:javascript nofilter}',
                    'pickup_time' : '{$sdek_office.WorkTime|escape:javascript nofilter}',
                    'city' : '{$sdek_office.City|escape:javascript nofilter}',
                    'pickup_address' : '{$sdek_office.FullAddress|escape:javascript nofilter}',
                    'pickup_phone' : '{$sdek_office.Phone|escape:javascript nofilter}',
                }
                {if !$smarty.foreach.st_loc_foreach.last},{/if}
                {/foreach}
            ]
        };

        $.ceEvent('on', 'ce.commoninit', function(context) {
            if (context.find('#' + map_options[{$shipping.shipping_id}].sdek_map_container).length) {
                $.ceSdekPickup('show', map_options[{$shipping.shipping_id}]);
            }
        });

    }(Tygh, Tygh.$));
</script>