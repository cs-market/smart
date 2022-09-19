{if $storages}
<td width="15%">
    <select name="product_data[user_price][{$new_key}][storage_id]">
        <option value="">---</option>
        {foreach from=$storages item="storage"}
            <option value="{$storage.storage_id}" {if $price.storage_id == $storage.storage_id}selected="_selected"{/if}>{$storage.storage}</option>
        {/foreach}
    </select>
</td>
{/if}
