{if $tg_allowed}
{if $user_data.chat_id}
    {include file="buttons/button.tpl" but_text=__('telegram.unsubscribe') but_href="telegram.unsubscribe&user_id=`$user_data.user_id`" but_role="action" but_target="_blank" but_meta="cm-post cm-ajax ty-btn__secondary"}
{else}
    {include file="buttons/button.tpl" but_text=__('telegram.assign_chat') but_href="telegram.assign_user&user_id=`$user_data.user_id`" but_role="action" but_target="_blank" but_meta="cm-post ty-btn__secondary" but_icon="ty-icon-aurora-telegram"}
{/if}
{/if}
