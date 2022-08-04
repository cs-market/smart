{$roles = ""|fn_get_user_role_list}
{if $roles}
<div class="control-group">
    <label class="control-label" for="elm_user_role">{__("user_role")}</label>
    <div class="controls">
        <select name="user_role" id="elm_user_role">
            <option value="">--</option>
            {foreach from=$roles item='role_name' key='role'}
                <option value="{$role}" {if $search.user_role == $role}selected="_selected"{/if}>{__($role_name)}</option>
            {/foreach}
        </select>
    </div>
</div>
{/if}
