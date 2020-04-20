{if $product_groups[$group_key].shippings[$shipping_id].module == 'calendar_delivery'}
<div>
	{if $settings.Appearance.calendar_date_format == "month_first"}
	    {assign var="date_format" value="%m/%d/%Y"}
	{else}
	    {assign var="date_format" value="%d/%m/%Y"}
	{/if}
	{$cid = $product_groups[$group_key].company_id}
	
	{$c_data = $cid|fn_get_company_data}
	{$min = $c_data|fn_calendar_get_nearest_delivery_day}

	{$min_date = "+{$min}"}
	{$default = "+{$min} day"|strtotime}

	{$default = $default|date_format:"`$date_format`"}
	{__("delivery_date")}: 
	{$cart.delivery_date.$cid|default:$default}
</div>
{/if}