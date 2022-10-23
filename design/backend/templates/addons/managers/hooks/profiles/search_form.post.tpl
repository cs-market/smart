{$managers = []|fn_get_managers}
<div class="control-group">
    <label class="control-label" for="elm_managers">{__("manager")}</label>
    <div class="controls">
    <select name="manager_users" id="elm_managers">
    <option value="">--</option>
        {foreach from=$managers item="manager"}
            <option value="{$manager.user_id}" {if $manager.user_id == $search.manager_users} selected="_selected"{/if}>{$manager.firstname} {$manager.lastname}</option>
        {/foreach}
    </select>
    </div>
</div>
