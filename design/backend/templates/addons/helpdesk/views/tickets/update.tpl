{* $Id$ *}

{capture name="mainbox"}

<form action="{""|fn_url}"  enctype="multipart/form-data" method="post" name="ticket_form" class="form-horizontal form-edit  cm-disable-empty-files cm-check-changes{*if ""|fn_check_form_permissions} cm-hide-inputs{/if*}">
<input type="hidden" name="ticket_id" value="{$ticket.ticket_id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

<div id="content_images">
<fieldset>
    <div class="control-group">
        <label for="subject" class="control-label cm-required">{__("subject")}:</label>
        <div class="controls">
            <input type="text" name="ticket_data[subject]" id="subject" size="30" value="{$ticket.subject}" class="input-text-large main-input" />
        </div>
    </div>
    <div class="control-group">
        <label for="mailbox_id" class="control-label cm-required">{__("mailbox_name")}:</label>
        <div class="controls">
            <select class="span5" name="ticket_data[mailbox_id]" id="mailbox_id">
                {foreach from=$mailboxes item='mailbox'}
                    <option value="{$mailbox.mailbox_id}" {if $mailbox.mailbox_id == $ticket.mailbox_id} selected="selected"{/if}>{$mailbox.mailbox_name}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("users_list")}</label>
        <div class="controls">
            {include file="pickers/users/picker.tpl" but_text=__("add_recipients_from_users") data_id="return_users" but_meta="btn" input_name="ticket_data[users]" item_ids=$ticket.users|array_keys placement="right"}
        </div>
    </div>
    {if !$ticket.ticket_id}
        <div class="control-group">
            <label class="control-label">{__("separate_ticket_by_users")}</label>
            <div class="controls">
                <input type="hidden" name="divide_ticket" value="N">
                <input type="checkbox" name="divide_ticket" value="Y" checked="checked">
            </div>
        </div>
        {include file="addons/helpdesk/views/tickets/components/message.tpl"}
    {/if}
</fieldset>
<!--content_general--></div>
{if $runtime.mode != "add"}
    {assign var="save" value=true}
    {assign var="but_name" value="dispatch[tickets.update]"}
{else}
    {assign var="but_name" value="dispatch[tickets.add]"}
{/if}
{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_role="submit-link" but_name=$but_name but_target_form="ticket_form" save=$save}
{/capture}
{** /Form submit section **}

</form>

{/capture}

{if $ticket.ticket_id}
    {capture name="mainbox_title"}
        {"{__("editing_ticket")}: `$ticket.subject`"}
    {/capture}
{else}
    {capture name="mainbox_title"}
        {__("new_ticket")}
    {/capture}
{/if}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox select_languages=$save buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
