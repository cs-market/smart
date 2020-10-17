{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="forms_form" id="forms_form" class="form-horizontal form-edit cm-processed-form">
<input type="hidden" name="fake" value="1" />
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{*assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"*}

{include file="common/pagination.tpl" save_current_page=true}

{if $forms}
<table width="100%" class="table table-middle">
<thead>
<tr>
	<th width="1%" class="center">{include file="common/check_items.tpl"}</th>
	<th width="3%">{__("id")}</th>
	<th width="8%">{__("page_name")}</th>
	<th width="8%">{__("user")}</th>
	<th width="10%">{__("date")}</th>
	<th width="20%">{__("comments")}</th>
	<th width="5%">&nbsp;</th>
	<th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
	{if $settings.Appearance.calendar_date_format == "month_first"}
		{assign var="date_format" value="n.j.Y G:i"}
	{else}
		{assign var="date_format" value="j.n.Y G:i"}
	{/if}
	{assign var="form_status_descr" value=$smarty.const.STATUSES_POPUP_PAGES|fn_get_simple_statuses}
	{foreach from=$forms item=form }
	<tr class="cm-row-status-{$status.status|lower}">
		<td class="center">
			<input type="checkbox" name="submit_ids[]" value="{$form.submit_id}" class="checkbox cm-item" /></td>
		<td>
			{$form.submit_id}
		</td>
		<td>
			{$form.form_name} <a class="icon-edit" href="{"pages.update&page_id=`$form.form_id`"|fn_url}"></a>
		</td>
		<td>
			<a href="{"profiles.update&user_id=`$form.user_id`"|fn_url}">{$form.user_id|fn_get_user_name}</a>
		</td>
		<td class="nowrap">
			{$date_format|date:$form.timestamp}
		</td>
		<td class="row-status">
			<textarea class="span4" rows="2" cols="25" name="forms_data[{$form.submit_id}][comments]" id="form_comments{$form.submit_id}">{strip}
				{$form.comments}
			{/strip}</textarea>
		</td>
		<td class="right nowrap">
			{capture name="tools_list"}
				<li>{btn type="text" class="cm-dialog-opener" data=['data-ca-target-id'=>'content_group_view', 'data-ca-target-form'=>'forms_form'] text=__("view_details") title=__("view_details") href="sent_forms.update?submit_id=`$form.submit_id`"}</li>
				{if $form.email}
				<li>{btn type="text" class="cm-dialog-opener" data=['data-ca-target-id'=>'content_group_answer', 'data-ca-target-form'=>'forms_form'] text=__("answer") title=__("answer") href="sent_forms.update.answer?submit_id=`$form.submit_id`"}</li>
				{/if}
				<li>{btn type="text" text=__("delete") href="sent_forms.delete?submit_id=`$form.submit_id`" class="cm-confirm cm-tooltip cm-ajax cm-ajax-force cm-ajax-full-render cm-delete-row" data=["data-ca-target-id" => "pagination_contents"] method="POST"}</li>
			{/capture}
			<div class="hidden-tools">
				{dropdown content=$smarty.capture.tools_list}
			</div>
		</td>
		<td class="right">
			{include file="common/select_popup.tpl" id=$form.submit_id status=$form.status items_status=$form_status_descr popup_additional_class="dropleft" object_id_name="submit_id" table="sent_forms"}

			
		</td>
	</tr>
	{/foreach}
</table>
{else}
	<p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl" save_current_page=true}
{capture name="buttons"}
		{capture name="tools_list"}
		{if $forms}
			<li>{btn type="delete_selected" dispatch="dispatch[sent_forms.m_delete]" form="forms_form"}</li>
		{/if}
		{/capture}
		{dropdown content=$smarty.capture.tools_list}
		{if $forms}
			{include file="buttons/save.tpl" but_name="dispatch[sent_forms.m_update]" but_role="submit-link" but_target_form="forms_form"}
		{/if}
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title=$_title content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons content_id="manage_users" no_sidebar=true}