{if $totals.totally_product_paid}
<tr>
    <td class="shift-right">{__("totally_product_paid")}:</td>
    <td>{include file="common/price.tpl" value=$totals.totally_product_paid}</td>
</tr>
{/if}
