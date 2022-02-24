{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'calendar_delivery'}
    <p>{__("delivery_date")}</p>

    {$hours = $smarty.now|date_format:'%H'}
    {$minutes = $smarty.now|date_format:'%M'}

    {$day = $smarty.now|date_format:'%w'}

    {$min_date = "+{$shipping.service_params.nearest_delivery_day}"}
    {$default = "+{$shipping.service_params.nearest_delivery_day} day"|strtotime}

    {$cid = $group.company_id}

    {$monday = ($shipping.service_params.monday_rule != 'Y' && (($hours >= 15 && $day == 6) || $day == 7))}

    {include file="addons/calendar_delivery/components/calendar.tpl" date_id="delivery_date`$cid`_`$shipping.shipping_id`" date_name="delivery_date[`$cid`]" date_val = $cart.delivery_date.$cid|default:$default|fn_parse_date min_date=$min_date sunday=$shipping.service_params.sunday_shipping saturday=$shipping.service_params.saturday_shipping monday=$monday limit_weekdays=$shipping.service_params.limit_weekday service_params=$shipping.service_params}

    {include
        file="addons/calendar_delivery/components/period.tpl"
        date_id="delivery_period`$cid`_`$shipping.shipping_id`"
        datapicker_id="delivery_date`$cid`_`$shipping.shipping_id`"
        date_name="delivery_period[`$cid`]"
        date_val = $cart.delivery_period.$cid
        period_start = $shipping.service_params.period_start
        period_finish = $shipping.service_params.period_finish
        period_step = $shipping.service_params.period_step
    }
{/if}
