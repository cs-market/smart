<div class="ty-rus-customer-geolocation__location-selector"
     data-ca-rus-customer-geolocation-element="location_selector"
>

    {if $locations|default:[]}
        <ul class="ty-rus-customer-geolocation__locations"
            data-ca-rus-customer-geolocation-element="locations_list"
        >
            {foreach $locations as $country_id => $country}
                <li class="ty-rus-customer-geolocation__location__country">
                    <h3 class="ty-rus-customer-geolocation__location__country__title">{$country.title}</h3>
                    <ul class="ty-rus-customer-geolocation__location__states">
                        {foreach $country.states as $state_id => $state}
                            <li class="ty-rus-customer-geolocation__location__state">
                                <h4 class="ty-rus-customer-geolocation__location__state__title">{$state.title}</h4>
                                <ul class="ty-rus-customer-geolocation__location__cities">
                                    {foreach $state.cities as $city}
                                        <li class="ty-rus-customer-geolocation__location__city">
                                            <a href="#"
                                               data-ca-rus-customer-geolocation-element="city"
                                               data-ca-rus-customer-geolocation-location-city="{$city}"
                                               data-ca-rus-customer-geolocation-location-state="{$state_id}"
                                               data-ca-rus-customer-geolocation-location-country="{$country_id}"
                                               class="cm-dialog-closer"
                                            >{$city}</a>
                                        </li>
                                    {/foreach}
                                </ul>
                            </li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    {else}
        <div class="ty-rus-customer-geolocation__map"
             data-ca-rus-customer-geolocation-element="map"
        >{* the map will be rendered here *}</div>

        <div class="ty-rus-customer-geolocation__map__load-error hidden"
             data-ca-rus-customer-geolocation-element="map_load_error_message">
            {__("rus_customer_geolocation.location_detection_disabled")}
        </div>

        <div class="buttons-container">
            {include file="buttons/button.tpl"
                     but_role="text"
                     but_meta="ty-btn__primary cm-dialog-closer ty-btn ty-float-right ty-rus-customer-geolocation__set-location pending"
                     but_text=__("ok")
            }
        </div>
    {/if}
</div>