<div id="content_order_history" class="hidden">
	{assign var="order_status_descr" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:true:true}
	
	{include file="common/pagination.tpl" id="order_history" search=$order_history_search}
	
    <table width="100%" class="table">
	<thead>
		<tr>
			<th width="10%">{__("date")}</th>
			<th width="40%">{__("action")}</th>
			<th width="10%">{__("user")}</th>
			<th width="40%">{__("comments")}</th>
		</tr>
	</thead>
	{foreach from=$order_history item="oh" key="key"}
	<tr valign="top">
		<td nowrap="nowrap">
			{$oh.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
		</td>
		<td class="nowrap">
			{assign var="_action" value="log_action_`$oh.action`"}
			{if $oh.action}{__($_action)|capitalize}{/if}<br>
			{if $oh.action == 'status'}
				{if !$order_status_descr[$oh.content.status_from] || !$order_status_descr[$oh.content.status_to]}
					{$oh.content.status}
				{else}
					{$order_status_descr[$oh.content.status_from]}&nbsp;->&nbsp;{$order_status_descr[$oh.content.status_to]}
				{/if}
			{elseif $oh.action == 'update'}	
				{if $oh.content.total_from != $oh.content.total_to}
					{include file="common/price.tpl" value=$oh.content.total_from}&nbsp;->&nbsp;{include file="common/price.tpl" value=$oh.content.total_to}
				{/if}
			{/if}
		<td nowrap="nowrap">
			{if $oh.user_id}
				<a href="{"profiles.update?user_id=`$oh.user_id`"|fn_url}" target="_blank">{$oh.lastname}{if $oh.lastname}&nbsp;{/if}{$oh.firstname}</a>
			{elseif $order_info.b_firstname || $order_info.b_lastname}	
				{$order_info.b_lastname}{if $order_info.b_lastname}&nbsp;{/if}{$order_info.b_firstname}
			{else}
				&mdash;
			{/if}
		</td>
		<td class="nowrap">
			{if $auth.is_root == 'Y' || $auth.user_id == $oh.user_id}
			<textarea name="update_order_history[{$oh.log_id}]" id="notes" cols="40" rows="5">{$oh.message}</textarea>
			{else}{$oh.message}{/if}
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="4"><p class="no-items">{__("no_data")}</p></td>
	</tr>
	{/foreach}
	</table>
	
	{include file="common/pagination.tpl" id="order_history" search=$order_history_search}
<!--content_order_history--></div>
