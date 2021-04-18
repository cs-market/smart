<div class="ticket">
{if $ticket}
    {capture name="title"}
		<span>{$ticket.subject}</span>
		<div class="ty-float-right">
			{capture name="new_message"}
			<form action="{""|fn_url}" method="POST" enctype="multipart/form-data" name="create_new_message" class='form-horizontal form-edit cm-disable-empty-files cm-check-changes collapse' id="create_new_message">
				<div class="cm-tabs-content" id="content_tab_add_post">
					<input type="hidden" name="ticket_data[ticket_id]" value="{$ticket.ticket_id}" />
					<input type ="hidden" name="redirect_url" value="{$config.current_url}" />

                    {include file="addons/helpdesk/views/tickets/components/new_message.tpl"}
				</div>

				<div class="buttons-container">
					{include file="buttons/button.tpl" but_text=__("post_message") but_name="dispatch[tickets.add_message]" but_meta="ty-btn ty-btn__primary"}
				</div>
			</form>
			{/capture}

			{include file="common/popupbox.tpl" act="notes" id="new_message" link_text=__("new_message") text=__("new_message") show_brackets=false content=$smarty.capture.new_message link_meta="ty-btn ty-btn__primary"}
		</div>
	{/capture}

	{if $ticket.messages}
		<div class="messages-group">
		{include file="common/pagination.tpl"}

		{foreach from=$ticket.messages item=message}
			<div class="panel {if $message.status == 'O'}panel-success{else}panel-info{/if}">
				<div class="panel-heading clearfix">
					<div class='ty-float-left'>{__("posted_by")}:&nbsp;{$message.user}</div>
					<div class='ty-float-right'>{"j/m/Y G:i"|date:$message.timestamp}</div>
				</div>
				<div class="panel-body">{$message.message nofilter}
					{if $message.files}
					<div class="panel panel-warning">
						<div class="panel-heading">
							<h3 class="panel-title">{__("files")}</h3>
						</div>
						<div class="panel-body">
							{foreach from=$message.files item="file"}
							<a href="{"tickets.get_file?file_id=`$file.file_id`"|fn_url}">{$file.filename}</a><hr/>
							{/foreach}
						</div>
					</div>
					{/if}
				</div>
			</div>
		{/foreach}
		{include file="common/pagination.tpl"}
		</div>
	{/if}
{/if}
</div>
