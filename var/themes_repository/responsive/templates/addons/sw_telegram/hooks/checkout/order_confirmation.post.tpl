{if !$auth.user_id|fn_sw_telegram_check_chat_id && $addons.sw_telegram.sw_bot_name && $addons.sw_telegram.tg_order_status_notification == 'Y'}
    {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("sw_telegram.confirm_noty") but_href="system_tg.set_user?order_id=order_`$order_info.order_id`" but_target="blank"}
    <br />
    {__("sw_telegram.confirm_noty_text_info")}
{/if}