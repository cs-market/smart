{if $addons.sw_telegram.tg_action_menu == 'Y' && $addons.sw_telegram.sw_bot_name}

    {if $auth.user_id && !$user_info.user_id|fn_sw_telegram_check_chat_id}
        <li class="ty-account-info__item ty-dropdown-box__item sw--telegram-noty-menu">
            {include file="buttons/button.tpl" but_meta="ty-btn" but_text=__("sw_telegram.add_user_noty") but_href="system_tg.set_user?user=user_`$user_info.user_id`" but_target="blank"}
        </li>
    {/if}

{/if}