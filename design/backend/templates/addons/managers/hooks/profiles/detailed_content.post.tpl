{if $user_data.user_type == 'C'}
    {if !""|fn_user_roles_is_manager}
    {include file="common/subheader.tpl" title=__("managers.managers")}
    <div class="control-group">
        <div class="controls cm-no-hide-input">
        <input class="cm-no-hide-input" type="hidden" name="user_id" value="{$id}" >
            {$extra_url = "&user_types[]=A&user_types[]=V&user_role[]=M&user_role[]=S&user_role[]=O"}
            {include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url view_mode="mixed" item_ids=$user_data.managers|array_column:'user_id' data_id="user_id" input_name="user_data[managers]"}
        </div>
    </div>
    {/if}
{elseif $user_data.user_id|fn_user_roles_is_management_user && !""|fn_user_roles_is_manager}
    {include file="common/subheader.tpl" title=__("managers.manager_users")}
    <div class="control-group">
        <div class="controls cm-no-hide-input">
        <input class="cm-no-hide-input" type="hidden" name="user_id" value="{$id}" >
            {$extra_url = "&user_type=C"}
            {include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url view_mode="mixed" item_ids=$user_data.users|array_column:'user_id' data_id="user_id" input_name="user_data[manager_users]"}
        </div>
    </div>
{/if}
