{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="notifications_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
{if $notifications}
<div class="table-wrapper">
    <table width="100%" class="table table-middle">
    <thead>
    <tr>
        <th width="1%">
            {include file="common/check_items.tpl"}</th>
        <th width="40%">{__("subject")}</th>
        <th>{__("date")}</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    {foreach from=$notifications item=notification}
    <tbody>
    <tr class="cm-row-status-{$notification.status|lower}">
        <td class="left">
            <input type="checkbox" name="notification_ids[]" value="{$notification.notification_id}" class="cm-item" /></td>
        <td>
            <a class="row-status" href="{"push_notifications.update?notification_id=`$notification.notification_id`"|fn_url}">{$notification.title}</a>
            {if $notification.company_id}<p class="muted"><small>{$notification.company_id|fn_get_company_name}</small></p>{/if}
        </td>

        <td class="nowrap">
            {if $notification.sent_date}
                {$notification.sent_date|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
            {else}
            &nbsp;-&nbsp;
            {/if}
        </td>

        <td class="nowrap right">
            {capture name="tools_list"}
                <li>{btn type="list" text=__("edit") href="push_notifications.update?notification_id=`$notification.notification_id`"}</li>
                <li>{btn type="list" class="cm-confirm" text=__("delete") href="push_notifications.delete?notification_id=`$notification.notification_id`" method="POST"}</li>
                {if $notification.user_ids}<li class="divider"></li>
                <li>{btn type="list" text=__("send") href="push_notifications.send?notification_id=`$notification.notification_id`" class="cm-post"}</li>{/if}
            {/capture}
            <div class="hidden-tools">
                {dropdown content=$smarty.capture.tools_list}
            </div>
        </td>
    </tr>
    {/foreach}
    </tbody>
    </table>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $notifications}
            <li>{btn type="delete_selected" dispatch="dispatch[push_notifications.m_delete]" form="notifications_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="push_notifications.add" hide_tools="true" title=__("add_notification")}
{/capture}

{include file="common/mainbox.tpl" title=$object_names content=$smarty.capture.mainbox select_languages=false buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}