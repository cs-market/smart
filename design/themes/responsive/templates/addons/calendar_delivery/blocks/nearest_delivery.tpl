{** block-description:calendar_delivery.nearest_delivery **}
<div class="ty-calendar-delivery__container">
    {__('calendar_delivery.nearest_delivery')}: <a class="cm-external-focus ty-calendar-delivery__date" data-ca-external-focus-id="{"delivery_date_`$group_key`_`$shipping.shipping_id`"}">{$shipping.service_params.nearest_delivery_day_text nofilter}</a>

    {$min_date = "+{$shipping.service_params.nearest_delivery_day}"}
    {$default = "+{$shipping.service_params.nearest_delivery_day} day"|strtotime}
    {include file="addons/calendar_delivery/components/calendar.tpl" date_id="delivery_date_`$group_key`_`$shipping.shipping_id`" date_name="delivery_date[`$group_key`]" date_val = $group.delivery_date|default:$default|fn_parse_date min_date=$min_date max_date=$shipping.service_params.max_date|default:"+30" service_params=$shipping.service_params extra='readonly="_readonly"'}
</div>
