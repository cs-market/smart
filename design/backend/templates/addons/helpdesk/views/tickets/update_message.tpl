{capture name="mainbox"}
{if $message}
<form id='form' action="{""|fn_url}" method="post" name="editing_message" class="form-horizontal form-edit  cm-disable-empty-files " enctype="multipart/form-data">
<div id="content_message">
    <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    <input type="hidden" name="message_id" value="{$message.message_id}" />
    <fieldset>
        {include file="addons/helpdesk/views/tickets/components/message.tpl"}
    </fieldset>
    {hook name="helpdesk:update_message"}
    {capture name="buttons"}
        {include file="buttons/save_cancel.tpl" but_role="submit-link" but_name='dispatch[tickets.update_message]' but_target_form="editing_message" save=true}
    {/capture}
    {/hook}
</div>
</form>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
{/capture}

{capture name="mainbox_title"}
    {"{__("editing_message")}: `$message.message_id` (`$message.user`)"}
{/capture}

{include file="common/mainbox.tpl" title=$smarty.capture.mainbox_title content=$smarty.capture.mainbox select_languages=$save buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
