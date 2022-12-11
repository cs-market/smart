{include file="common/subheader.tpl" title=__("trading_network")}

{if $user_data.user_role == 'UserRoles::CUSTOMER'|enum}
<div class="control-group">
    <label class="control-label" for="trading_network">{__("trading_network")}:</label>
    <div class="controls">
        {include
            file="pickers/users/picker.tpl"
            but_text=__("choose")
            extra_url="&user_type=C&user_role=N"
            data_id="trading_network"
            but_meta="btn"
            input_name="user_data[network_id]"
            item_ids=$user_data.network_id
            user_info=$user_data.network_id|fn_get_user_short_info
            display="radio"
            view_mode="single_button"
        }
    </div>
</div>
{elseif $user_data.user_role == 'N'}
    {include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" item_ids=$network_users data_id="0" input_name="user_data[network_users]" extra_url="&user_type=C&user_role=C"}
{/if}
