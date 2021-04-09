<div class="control-group">
    <label for="elm_company_location" class="control-label">{__("vendor_locations.location")}:</label>
    <div class="controls">
        {$place_id = null}
        {if $company_data.vendor_location}
            {$place_id=$company_data.vendor_location->getPlaceId()}
        {/if}
        <input
                type="text"
                id="elm_company_location"
                class="cm-geocomplete input-large{if !$is_allowed_to_update_companies} cm-no-hide-input{/if}"
                data-ca-geocomplete-type="address"
                data-ca-geocomplete-place-id="{$place_id}"
                data-ca-geocomplete-value-elem-id="elm_company_vendor_location_value"
                {if !$is_allowed_to_update_companies}disabled="disabled"{/if}
        />

        <input type="hidden" name="company_data[vendor_location]" id="elm_company_vendor_location_value" disabled="disabled" />
        <p class="muted description">{__("tt_addons_vendor_locations_hooks_companies_shipping_address_post_vendor_locations.location")}</p>
    </div>
</div>
