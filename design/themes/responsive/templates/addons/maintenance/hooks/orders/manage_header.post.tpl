{if $orders|array_column:'tracking_link'|array_filter}
    <th>{__("maintenance.track_order")}</th>
{/if}
