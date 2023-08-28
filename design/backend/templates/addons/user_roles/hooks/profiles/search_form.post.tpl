{$roles = ""|fn_get_user_role_list}
{if $roles}
<div class="control-group">
    <label class="control-label" for="elm_user_role">{__("user_role")}</label>
    <div class="controls">
        {foreach from=$_u_type|fn_get_user_role_list item="role_name" key="role"}
            <label class="checkbox" for="elm_role_{$role}">
            <input type="checkbox" name="user_role[{$role}]" id="elm_roles_{$role}" {if $role|in_array:$search.user_role}checked="checked"{/if} value="{$role}" />
            {__($role_name)}</label>
        {/foreach}
    </div>
</div>
{/if}
