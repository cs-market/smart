<div class="group">
    {$managers = []|fn_smart_distribution_get_managers}
    <div class="control-group">
        <label class="control-label" for="manager">{__("manager")}</label>
        <div class="controls">
        <select name="managers" id="manager">
            <option value="">--</option>
            {foreach from=$managers item=manager}
                <option value="{$manager.user_id}" {if $search.managers == $manager.user_id}selected="selected"{/if}>{$manager.name}</option>
            {/foreach}
        </select>
        </div>
    </div>
</div>
