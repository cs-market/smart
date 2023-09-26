{if $tg_allowed}
    {include file="common/subheader.tpl" title=__("telegram.telegram")}
    <div class="control-group">
        <label class="control-label" for="chat_id">{__("telegram.chat_id")}</label>
        <div class="controls">
            <input type="text" name="user_data[chat_id]" value="{$user_data.chat_id}">
            {if !$user_data.chat_id}{include file="buttons/button.tpl" but_text=__('telegram.assign_chat') but_href="telegram.assign_user&user_id=`$user_data.user_id`" but_role="action" but_target="_blank" but_meta="cm-post"}{/if}
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="events_subscribed">{__("telegram.events_subscribed")}</label>
        <div class="controls">
            <input type="hidden" name="user_data[tg_events_subscribed]" value="N">
            <input type="checkbox" name="user_data[tg_events_subscribed]" value="Y" {if $user_data.tg_events_subscribed == "YesNo::YES"|enum}checked="_checked"{/if}>
        </div>
    </div>
{/if}
