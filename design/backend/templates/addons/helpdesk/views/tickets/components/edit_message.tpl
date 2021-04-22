{if $message}
<form id='form' action="{""|fn_url}" method="post" name="editing_message" class="form-horizontal form-edit  cm-disable-empty-files " enctype="multipart/form-data">
<div id="content_message">
	<input type="hidden" name="redirect_url" value="{$config.current_url}" />
	<input type="hidden" name="message_id" value="{$message.message_id}" />
	<fieldset>
	<div class="control-group">
		<label for="helpdesk_message" class="control-label">{__('text')}:</label>
		<div class="controls">
			<textarea id="helpdesk_message" name="ticket_data[message]" cols="55" rows="8" class="cm-wysiwyg input-large">{$message.message}</textarea>
		</div>
	</div>
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
