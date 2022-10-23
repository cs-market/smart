<div class="sidebar-field">
    <label for="elm_manager">{__("manager")}</label>
    <div class="break">
        <select name="dynamic_conditions[managers]" id="elm_manager">
            <option value="">--</option>
            {$managers = ""|fn_get_managers}
            {foreach from=$managers item="manager"}
                <option value="{$manager.user_id}" {if $dynamic_conditions.managers == $manager.user_id} selected="selected" {/if}>{$manager.firstname} {$manager.lastname}</option>
            {/foreach}
        </select>
    </div>
</div>
