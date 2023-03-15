{if fn_allowed_for("MULTIVENDOR") && $user_data.user_type == 'V'}
    {$label_noty_tg = __("sw_telegram.confirm_noty_vendor")}
{else}
    {$label_noty_tg = __("sw_telegram.confirm_noty")}
{/if}

{$allow_noty = false}

{if $user_data.user_type == 'C' 
|| $auth.user_type == 'A' 
|| (fn_allowed_for("MULTIVENDOR") 
    && $user_data.user_type == 'V' 
    && $addons.sw_telegram.tg_allow_for_vendor == 'Y')
}

    {$allow_noty = true}

{/if}

{if $allow_noty}
    {include file="common/subheader.tpl" title=__("sw_telegram.notify_telegram_user_settings")}

    {if fn_allowed_for("MULTIVENDOR") || $auth.user_type == 'A' }
        <div class="control-group">
            <label class="control-label">{__("sw_telegram_get_id")}
                {include file="common/tooltip.tpl" tooltip=__("sw_telegram_get_id_chat_tooltip")}:</label>
            <div class="controls">
                <a class="btn btn-primary" target="_blank"
                    href="{"system_tg.set_user&user=user_`$user_data.user_id`"|fn_url:'C'}">{__("sw_telegram_get_id_chat")}</a>
            </div>
        </div>
    {/if}

    <div class="control-group">
        <label for="chat_id" class="control-label">{__("sw_telegram.chat_id")}
            {include file="common/tooltip.tpl" tooltip=__("sw_telegram.chat_id_ttc")}</label>
        <div class="controls">
            <input class="input-large" type="text" name="user_data[chat_id]" id="chat_id" size="30"
                value="{$user_data.chat_id}" />
        </div>
    </div>

    <div class="control-group">
        <label for="noty_tg" class="control-label">{$label_noty_tg}</label>
        <div class="controls">
            <input class="checkbox" type="hidden" name="user_data[noty_tg]" value="N">
            <input class="checkbox" type="checkbox" name="user_data[noty_tg]" id="noty_tg"
                {if $user_data.noty_tg == 'Y'}checked="checked" {/if} value="Y" />
        </div>
    </div>
{/if}