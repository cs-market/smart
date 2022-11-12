{if $items}
    {$columns = $block.properties.number_of_columns|default:3}

    {if $items|count < $columns} {$columns = $items|count} {/if}

    <div id="mobile_app_{$block.snapping_id}" class="ty-mobile-app-links__wrapper" style="--columns: {$columns}">
        {foreach from=$items key="type" item="link"}
            <a href="{$link|fn_url}" target="_blank" class="ty-mobile-app-links ty-mobile-app-links__{$type}"></a>
        {/foreach}
    </div>
{/if}
