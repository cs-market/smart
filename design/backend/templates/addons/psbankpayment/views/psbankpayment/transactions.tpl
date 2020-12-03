{capture name="mainbox"}
    {capture name="tabsbox"}
{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}
{if $transactions}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th>{__("addons.psbankpayment.order_number")}</th>
    <th>{__("addons.psbankpayment.order_amount")}</th>
    <th>{__("addons.psbankpayment.date")}</th>
    <th>{__("addons.psbankpayment.email")}</th>
    <th>{__("addons.psbankpayment.name")}</th>
    <th>RRN</th>
    <th>{__("addons.psbankpayment.status")}</th>
	<th>{__("addons.psbankpayment.action")}</th>
	<th></th>
</tr>
</thead>
<tbody>
	{assign var=action_status value=[1,21,12, 14, 22]}
	{assign var=confirm_status value=[1,21, 14]}
	{assign var=cancel_status value=[12, 22]}
	{assign var="extra_status" value=$config.current_url|escape:"url"}
	{foreach from=$transactions item=item key=key name=name}
	<tr id="transaction_{$item.ORDER_ID}">
		<td>{$item.ORDER_ID}</td>
		<td>{$item.AMOUNT}</td>
		<td>{$item.DATE}</td>
		<td>{$item.EMAIL}</td>
		<td>{$item.NAME}</td>
		<td>{$item.RRN}</td>
		<td>{assign  var=status value={__("addons.psbankpayment.status_{$item.STATUS}")}}
		{$status}
		{if $item.RCTEXT}
			({$item.RCTEXT})
		{/if}
		</td>
		<td>

		{if $item.STATUS|in_array:$action_status && $item.AMOUNT>0}
			<form method="POST" action="{"psbankpayment.action?return_url=`$extra_status`"|fn_url}" class="cm-ajax">
				<input type="text" name="sum" value="{$item.AMOUNT}" style="width:65px">
				<input type="hidden" name="order_id" value="{$item.ORDER_ID}">
				<input type="hidden" name="result_ids" value="transaction_{$item.ORDER_ID}" >
				<br>
			<div style="white-space: nowrap;">
			{if $item.STATUS|in_array:$confirm_status }
					<button class="btn btn-primary" type="submit" name="trtype" value="14">{__("addons.psbankpayment.return")}</button>
			{/if}
			 {if $item.STATUS|in_array:$cancel_status }
					<button class="btn btn-primary" type="submit" name="trtype" value="22">{__("addons.psbankpayment.cancel")}</button>
					<button calss="btn btn-primary cm-submit" type="submit" name="trtype" value="21">{__("addons.psbankpayment.complete")}</button>
			{/if}
			</div>
			</form>
		{/if}
		</td>
		<td>
			<a href="{"psbankpayment.history?order_id=`$item.ORDER_ID`"|fn_url}">{__("addons.psbankpayment.history")}</a>
		</td>

	<!--transaction_{$item.ORDER_ID}--></tr>
	{/foreach}
</tbody>
</table>
{else}
	<p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl" div_id=$smarty.request.content_id}

    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}
{/capture}

{capture name="sidebar"}
	{include file="common/saved_search.tpl" dispatch="psbankpayment.transactions"}
	<div class="sidebar-row">
	<h6>{__("search")}</h6>
	<form action="{""|fn_url}" name="transactions_search_form" method="get" class="cm-disable-empty">
	{capture name="simple_search"}
    <div class="sidebar-field">
        <label>{__("addons.psbankpayment.order_number")}</label>
        <input type="text" name="filter_order_id" size="20" value="{$search.order_id}" />
    </div>

    <div class="sidebar-field">
        <label>RRN</label>
        <input type="text" name="filter_rrn" size="20" value="{$search.rrn}" />
    </div>

	{/capture}
    {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search="" dispatch="psbankpayment.transactions" }
	</form>
</div>
{/capture}

{include file="common/mainbox.tpl" title=__("addons.psbankpayment.transactions") content=$smarty.capture.mainbox  sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons}