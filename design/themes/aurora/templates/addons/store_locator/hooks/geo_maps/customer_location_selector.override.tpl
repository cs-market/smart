{$return_url = $return_url|escape:'url'}
{if $locations}
    {$c_url = $config.current_url|urlencode}
    <ul class="ty-store-locator__geolocation__locations">
        {foreach $locations as $country_id => $country}
            <li class="ty-store-locator__geolocation__location__country">
                <h3 class="ty-store-locator__geolocation__location__country__title">{$country.title}</h3>
                <ul class="ty-store-locator__geolocation__location__states">
                    {foreach $country.states as $state_id => $state}
                        <li class="ty-store-locator__geolocation__location__state">
                            <h4 class="ty-store-locator__geolocation__location__state__title">{$state.title}</h4>
                            <ul class="ty-store-locator__geolocation__location__cities">
                                {foreach $state.cities as $city}
                                    <li class="ty-store-locator__geolocation__location__city">
                                        <a class="cm-post" href="{"geo_maps.set_location"|fn_url}{"&location[country]=`$country_id`&location[state_code]=`$state_id`&location[locality_text]=`$city`&return_url=`$return_url`"}"
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
{/if}
