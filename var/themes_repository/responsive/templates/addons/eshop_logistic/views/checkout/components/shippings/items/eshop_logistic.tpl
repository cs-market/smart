<label for="eshop_service_terminal_{$store.code}_{$shipping.shipping_id}"
       class="ty-one-store js-pickup-search-block-{$group_key} {if $selected_terminal_id == $store.code || $store_count == 1}ty-sdek-office__selected{/if} "
>
    <input
        type="radio"
        name="eshop_service_terminal[{$group_key}][{$shipping.shipping_id}]"
        value="{$store.code}"
        {if $selected_terminal_id == $store.code || $store_count == 1}
            checked="checked"
        {/if}
        class="ty-one-store__radio ty-one-store__radio--{$group_key} cm-sl-pickup-select-store"
        id="eshop_service_terminal_{$store.code}_{$shipping.shipping_id}"
        data-ca-pickup-select-store="true"
        data-ca-shipping-id="{$shipping.shipping_id}"
        data-ca-group-key="{$group_key}"
        data-ca-location-id="{$store.code}"
    />

    <div class="ty-sdek-store__label ty-one-store__label">
        <p class="ty-one-store__name">
            <span class="ty-one-store__name-text">{$store.address nofilter}</span>
        </p>

        <div class="ty-one-store__description">
            {if $store.address}
                <span class="ty-one-office__address">{$store.code nofilter}</span>
                <br />
            {/if}
            {if $store.workTime}
                <span class="ty-one-office__worktime">{$store.workTime nofilter}</span>
                <br />
            {/if}
            {if $store.note}
                <span class="ty-one-office__worktime">{__('lite_checkout.nearest_station')}: {$store.note|truncate:100 nofilter}</span>
                <br />
            {/if}
            {if $store.phone}
                <span class="ty-one-office__worktime">{$store.phone}</span>
                <br />
            {/if}
        </div>
    </div>
</label>
