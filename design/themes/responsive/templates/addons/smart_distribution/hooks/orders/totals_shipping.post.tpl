{if $order_info.product_groups.0.package_info_full.packages.0.weight}
    </td></tr>
    <tr class="ty-orders-summary__row"><td>{__("weight")}</td><td>{$order_info.product_groups.0.package_info_full.packages.0.weight} {$settings.General.weight_symbol nofilter}
{/if}