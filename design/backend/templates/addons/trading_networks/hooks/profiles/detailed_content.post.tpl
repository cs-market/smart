{include file="common/subheader.tpl" title=__("trading_network")}
<div class="control-group">
    <label class="control-label" for="trading_network">{__("trading_network")}</label>
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
