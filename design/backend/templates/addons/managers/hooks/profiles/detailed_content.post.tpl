{if !""|fn_user_roles_is_manager}
    {if $user_data.user_type == 'C'}
        {include file="common/subheader.tpl" title=__("managers.managers")}
        <div class="control-group">
            <div class="controls cm-no-hide-input">
            <input class="cm-no-hide-input" type="hidden" name="user_id" value="{$id}" >
                {$extra_url = "&user_types[]=A&user_types[]=V&user_role=M"}
                {include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url view_mode="mixed" item_ids=$user_data.managers|array_column:'user_id' data_id="user_id" input_name="user_data[managers]"}
            </div>
        </div>
    {/if}
{/if}
