{if $order_info.shipping.0.module == 'calendar_delivery' && $order_info.delivery_date}
<div class="control-group">
    <div class="control-label">{__('delivery_date')}</div>
    <div id="tygh_delivery_date" class="controls">{$order_info.delivery_date|date_format:"`$settings.Appearance.date_format`"}</div>
</div>
{/if}