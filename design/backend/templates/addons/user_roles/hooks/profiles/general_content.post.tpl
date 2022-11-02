{assign var="_u_type" value=$smarty.request.user_type|default:$user_data.user_type}

{if $uid != 1}
    <div class="control-group">
        <label for="user_role" class="control-label cm-required">{__("user_role")}:</label>
        <div class="controls">
        <select id="user_role" name="user_data[user_role]">
            {foreach from=$_u_type|fn_get_user_role_list item="role_name" key="role"}
                <option value="{$role}" {if $user_data.user_role == $role}selected="selected"{/if}>{__($role_name)}</option>
            {/foreach}
        </select>
        </div>
    </div>
{/if}
