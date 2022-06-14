{if $order_info.shipping.0.module == 'calendar_delivery' && $order_info.delivery_date}
<div class="control-group">
    <div class="control-label">{__('delivery_date')}</div>
    <div id="tygh_delivery_date" class="controls">{$order_info.delivery_date|date_format:"`$settings.Appearance.date_format`"}</div>
</div>

    {if $order_info.delivery_period}
    <div class="control-group">
        <div class="control-label">{__('calendar_delivery.delivery_period')}</div>
        <div id="tygh_delivery_period" class="controls">{$order_info.delivery_period}</div>
    </div>
    {/if}
{/if}

{if $order_info.documents_originals}
    <div class="control-group">
        <div class="control-label">{__('calendar_delivery.documents_originals')}</div>
        <div class="controls">
            <input type="checkbox" checked="_checked" disabled="_disabled">
        </div>
    </div>
{/if}
