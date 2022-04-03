{if $totals.totally_product_paid}
<tr>
    <td class="shift-right">{__("totally_product_paid")}:</td>
    <td>{include file="common/price.tpl" value=$totals.totally_product_paid}</td>
</tr>
{/if}
{if $totals.unique_sku}
<tr>
    <td class="shift-right">{__("unique_sku")}:</td>
    <td>{$totals.unique_sku}</td>
</tr>
{/if}
{if $totals.unique_sku_per_order}
<tr>
    <td class="shift-right">{__("unique_sku_per_order")}:</td>
    <td>{$totals.unique_sku_per_order}</td>
</tr>
{/if}
{if $totals.free_orders}
<tr>
    <td class="shift-right">{__("free_orders")}:</td>
    <td>{$totals.free_orders}</td>
</tr>
{/if}
