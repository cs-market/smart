{capture name="mainbox"}
    {capture name="tabsbox"}
{if $transactions}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th>{__("addons.psbankpayment.history_timestamp")}</th>
    <th>{__("addons.psbankpayment.history_amount")}</th>
    <th>{__("addons.psbankpayment.history_org_amount")}</th>
    <th>{__("addons.psbankpayment.history_trtype")}</th>
    <th>{__("addons.psbankpayment.history_result")}</th>
    <th>{__("addons.psbankpayment.history_rc")}</th>
	<th>{__("addons.psbankpayment.history_authcode")}</th>
	<th>{__("addons.psbankpayment.history_rrn")}</th>
	<th>{__("addons.psbankpayment.history_int_ref")}</th>
	<th>{__("addons.psbankpayment.history_name")}</th>
	<th>{__("addons.psbankpayment.history_card")}</th>
</tr>
</thead>
<tbody>
	{foreach from=$transactions item=item key=key name=name}
	{assign var=ts value=$item.TIMESTAMP}
	<tr>
		<td>{$ts[0]}{$ts[1]}{$ts[2]}{$ts[3]}-{$ts[4]}{$ts[5]}-{$ts[6]}{$ts[7]} {$ts[8]}{$ts[9]}:{$ts[10]}{$ts[11]}:{$ts[12]}{$ts[13]}</td>
		<td>{$item.AMOUNT}</td>
		<td>{$item.ORG_AMOUNT}</td>
		<td>
		{assign  var=trtype value={__("addons.psbankpayment.trtype_{$item.TRTYPE}")}}
		{$trtype}</td>
		<td>{$item.RESULT}</td>
		<td>{$item.RC} {$item.RCTEXT}</td>
		<td>{$item.AUTHCODE}</td>
		<td>{$item.RRN}</td>
		<td>{$item.INT_REF}</td>
		<td>{$item.NAME}</td>
		<td>{$item.CARD}</td>
		<td>
	</tr>
	{/foreach}
</tbody>
</table>
{else}
	<p class="no-items">{__("no_data")}</p>
{/if}
    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}
{/capture}

{include file="common/mainbox.tpl" title=__("addons.psbankpayment.order_history", ["[order_id]" => $order_id]) content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}