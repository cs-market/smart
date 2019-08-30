<select id="elm_state_{$id}"
        class="cm-state cm-location-{$id} ty-rus-customer-geolocation-location__state ty-rus-customer-geolocation-location__state--select"
        name="{$name}"
>
    <option value="">- {__("select_state")} -</option>
    {foreach $states as $state_id => $state}
        <option {if $state_id == $selected}selected{/if}
                value="{$state_id}"
        >{$state.state}</option>
    {/foreach}
</select>
<input type="text"
       id="elm_state_{$id}_d"
       name="{$name}"
       value="{$selected}"
       disabled="disabled"
       class="cm-state cm-location-{$id} ty-rus-customer-geolocation-location__state ty-rus-customer-geolocation-location__state--input hidden"
/>