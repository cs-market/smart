{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'calendar_delivery'}
	<p>{__("delivery_date")}</p>
	{$default = "+1 day"|strtotime}

	{include file="addons/calendar_delivery/components/calendar.tpl" date_id="delivery_date" date_name="delivery_date[`$group_key`]" date_val = $cart.delivery_date.$group_key|default:$default min_date="+1"}
{/if}