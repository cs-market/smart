{capture name="mainbox"}

<div id="content_ticket">

{if !$hide_new_message}
{include file="common/subheader.tpl" title=__("new_message") target='#create_new_message' meta='collapsed'}

<form action="{""|fn_url}" method="POST" enctype="multipart/form-data" name="create_new_message" class='form-horizontal form-edit cm-disable-empty-files cm-check-changes cm-ajax1 collapse' id="create_new_message">
	<div class="cm-tabs-content clearfix" id="content_tab_add_post" style="padding-bottom: 30px;">
		<input type="hidden" value="pagination_contents" name="result_ids">
		<input type="hidden" name="ticket_data[ticket_id]" value="{$ticket.ticket_id}" />
		<input type ="hidden" name="redirect_url" value="{$config.current_url}" />

		<div style="width: 80%; float:left;">

			<div class="control-group" style="">
				<label for="helpdesk_message" class="control-label cm-required">{__("new_message")}:</label>
				<div class="controls">
					<textarea id="helpdesk_message" name="ticket_data[message]" cols="55" rows="8" class="cm-wysiwyg input-large">{$ticket_data.message}</textarea>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="box_new_file">{__("files")}:</label>
				<div id="box_new_file" class="margin-top controls">
					<div class="clear cm-row-item">
						{assign var="key" value=$image_key|default:"0"}
						<div class="float-left">{include file="common/fileuploader.tpl" hide_server=true var_name="ticket_data[`$key`]"}</div>
						<div class="buttons-container">{include file="buttons/multiple_buttons.tpl" item_id="new_file"}</div>
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="buttons-container btn-hover controls" style="position: relative;">
					{include file="buttons/button.tpl" but_text=__("post_message") but_name="dispatch[tickets.add_message]" but_meta="btn-primary" but_id="post_message"}
					<script>
						$(document).ready(function () {
							$('#post_message').click(function () {
								$('#content_ticket > .subheader').click();
							});
						});
					</script>
                    {hook name="helpdesk:submit_button"}{/hook}
				</div>
			</div>
		</div>

		<div style="margin-right: 10px; margin-top: 10px;" class="open pull-right">
			<ul id="template_picker" class="dropdown-menu" style="position: static;">
			{foreach from=$templates.Y item=template }
				<li id="template_{$template.template_id}" data="{$template.template }"><a>{$template.name}</a></li>
			{/foreach}
			<li class="divider"></li>
			{foreach from=$templates.N item=template }
				<li id="template_{$template.template_id}" data="{$template.template }"><a>{$template.name}</a></li>
			{/foreach}
			</ul>
		</div>
		{literal}
		<script>
			$(document).ready(function () {
				$('#template_picker li').click(function () {
					template = $( this ).attr('data');
					$("#helpdesk_message").ceEditor("val", $("#helpdesk_message").ceEditor("val") + template);
				});
			});
		</script>
		{/literal}
	</div>
</form>
{/if}
{include file="addons/helpdesk/views/tickets/components/ticket_log.tpl" messages=$ticket.messages}
{/capture}

{capture name="buttons"}

{/capture}

{include file="common/mainbox.tpl" title=$ticket.subject|default:$subject content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra buttons=$smarty.capture.buttons no_sidebar=true}
