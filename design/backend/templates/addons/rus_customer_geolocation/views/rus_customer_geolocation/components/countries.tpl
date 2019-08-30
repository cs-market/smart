<select id="elm_country_{$id}"
        class="cm-country cm-location-{$id} ty-rus-customer-geolocation-location__country"
        name="{$name}"
>
    <option value="">- {__("select_country")} -</option>
    {foreach $countries as $country_id => $country}
        <option {if $country_id == $selected}selected{/if}
                value="{$country_id}"
        >{$country}</option>
    {/foreach}
</select>