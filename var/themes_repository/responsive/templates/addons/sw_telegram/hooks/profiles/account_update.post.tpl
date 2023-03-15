{if $addons.sw_telegram.tg_action_profile == 'Y'}
    {if $user_data.user_type == 'C' && $addons.sw_telegram.sw_bot_name && $addons.sw_telegram.tg_order_status_notification == 'Y'}
        <div class="ty-control-group">
            <input class="checkbox" type="hidden" name="user_data[noty_tg]" value="N">
            <input id="user_data_check_tg_{$user_data.user_id}" {if $user_data.noty_tg == 'Y'}checked="checked" {/if}
                class="checkbox" type="checkbox" name="user_data[noty_tg]" value="Y">
            <label for="user_data_check_tg_{$user_data.user_id}">{__("sw_telegram.confirm_noty")}.</label>
        </div>

        {if  $user_data.noty_tg == 'Y' && !$user_data.user_id|fn_sw_telegram_check_chat_id && $addons.sw_telegram.sw_bot_name}
            {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("sw_telegram_get_id") but_href="system_tg.set_user?user=user_`$user_data.user_id`" but_target="blank"}
        {/if}
    {/if}
{/if}