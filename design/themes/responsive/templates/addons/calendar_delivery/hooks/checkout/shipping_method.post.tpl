{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'calendar_delivery'}
	<p>{__("delivery_date")}</p>
	{$hours = $smarty.now|date_format:'%H'}
	{$minutes = $smarty.now|date_format:'%M'}

	{$day = $smarty.now|date_format:'%w'}
	{$c_data = $group.company_id|fn_get_company_data}

	{$min = '1'}
	{if $c_data|fn_validate_tomorrow_rule}
		{$min = $min + 1}
	{/if}
	{$min_date = "+{$min}"}
	{$default = "+{$min} day"|strtotime}

	{$cid = $group.company_id}

	{$saturday = ($c_data.saturday_rule != 'Y' && ($hours >= 15 && $day == 6) || $day == 7)}
	{include file="addons/calendar_delivery/components/calendar.tpl" date_id="delivery_date`$cid`" date_name="delivery_date[`$cid`]" date_val = $cart.delivery_date.$cid|default:$default|fn_parse_date min_date=$min_date sunday=$c_data.sunday_shipping saturday=$saturday}
{/if}