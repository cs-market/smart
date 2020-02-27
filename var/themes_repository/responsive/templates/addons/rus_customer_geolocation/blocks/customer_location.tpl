{** block-description:block_rus_customer_geolocation_customer_location **}

{if $addons.rus_customer_geolocation.status == "A"}
    {$location = $smarty.session.settings.rus_customer_geolocation_location.value}
    <div class="ty-rus-customer-geolocation"
         data-ca-rus-customer-geolocation-is-location-detected="{if $location.is_detected}true{else}false{/if}"
         data-ca-rus-customer-geolocation-element="location_block"
         id="rus_customer_geolocation_location_block_{$block.snapping_id}"
    >
        {capture name="rus_customer_geolocation_popup_opener"}
            {strip}
                <span data-ca-rus-customer-geolocation-element="location"
                      class="ty-rus-customer-geolocation__location"
                >
                {$location.city|default:__("rus_customer_geolocation.your_city")}
            </span>
            {/strip}
        {/capture}


        {include file="common/popupbox.tpl"
                 href="rus_customer_geolocation.get_locations"
                 link_text=$smarty.capture.rus_customer_geolocation_popup_opener
                 link_icon="ty-icon-location-arrow"
                 link_meta="ty-rus-customer-geolocation__opener"
                 text=__("rus_customer_geolocation.select_your_city")
                 id="rus_customer_geolocation_geolocation_dialog"
        }
    <!--rus_customer_geolocation_location_block_{$block.snapping_id}--></div>
{/if}