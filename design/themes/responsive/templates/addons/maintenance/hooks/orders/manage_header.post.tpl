{if $orders|array_column:'tracking_link'|array_filter}
    <th>{__("maintenance.tracking_link")}</th>
{/if}
