{if $notification.notification_id}
    {assign var="id" value=$notification.notification_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" enctype="multipart/form-data" name="notifications_form" class="form-horizontal form-edit ">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="notification_id" value="{$id}" />

<div class="control-group">
    <label class="control-label cm-required" for="elm_notification_subject">{__("subject")}</label>
    <div class="controls">
        <input type="text" name="notification_data[title]" id="elm_notification_subject" value="{$notification.title}" size="40" class="input-large" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_notification_descr_html">{__("body")}</label>
    <div class="controls">
        <textarea id="elm_notification_descr_html" name="notification_data[body]" cols="35" rows="8" class="input-large">{$notification.body}</textarea>
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("users")}</label>
    <div class="controls">
        {include file="pickers/users/picker.tpl" but_text=__("add_recipients_from_users") data_id="return_users" but_meta="btn" input_name="notification_data[user_ids]" item_ids=$notification.user_ids placement="right" extra_url="&mobile_app=Y"}
    </div>
</div>

{if $notification_type != $smarty.const.notification_TYPE_TEMPLATE}
    <div class="control-group">
        <label class="control-label" for="elm_notification_test_send">{__("send_to_test_email")}</label>
        <div class="controls">
            <div class="input-append">
                <input type="text" name="test_email" id="elm_notification_test_send" value="" class="input-medium" />
                {include file="buttons/button.tpl" but_text=__("send") but_name="dispatch[notifications.test_send]" but_id="test_send_button" but_meta="cm-ajax"}
            </div>
        </div>
    </div>
{/if}

</form>

{capture name="buttons"}

{include file="buttons/button.tpl" but_text=__("save_and_send") but_name="dispatch[push_notifications.update.send]" but_role="action" but_meta="cm-submit" but_target_form="notifications_form"}
{include file="buttons/save.tpl" but_name="dispatch[push_notifications.update]" but_role="submit-link" but_target_form="notifications_form"}
{/capture}
{/capture}

    {assign var="object_name" value=__("push_notification")|lower}


{if !$id}
    {include file="common/mainbox.tpl" title="{__("new")}: `$object_name`" content=$smarty.capture.mainbox select_languages=false buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
{else}
    {include file="common/mainbox.tpl"
        title_start=__("editing")
        title_end=$notification.notification
        content=$smarty.capture.mainbox
        select_languages=false
        buttons=$smarty.capture.buttons
        sidebar=$smarty.capture.sidebar}
{/if}
