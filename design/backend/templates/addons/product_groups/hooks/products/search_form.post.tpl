{$groups = []|fn_get_product_groups}
{if $groups}
<div class="control-group" id="elm_product_group">
    <label class="control-label" for="product_group">{__("product_groups.product_group")}</label>
    <div class="controls">
        <select name="group_id" id="product_group">
            <option value="">--</option>
            {foreach from=$groups item="group"}
                <option value="{$group.group_id}" {if $search.group_id == $group.group_id}selected="_selected"{/if}>{$group.group}</option>
            {/foreach}
        </select>
    </div>
</div>
{/if}
