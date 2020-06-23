{if $order_info.delivery_date}
    {if $settings.Appearance.calendar_date_format == "month_first"}
        {assign var="date_format" value="%m/%d/%Y"}
    {else}
        {assign var="date_format" value="%d/%m/%Y"}
    {/if}
    </td></tr>
    <tr class="ty-orders-summary__row"><td>{__("delivery_date")}</td><td>{$order_info.delivery_date|date_format:"`$date_format`"}
    {if $order_info.delivery_period}
        </td></tr><tr class="ty-orders-summary__row"><td>{__("calendar_delivery.delivery_period")}</td><td>{$order_info.delivery_period}
    {/if}
{/if}