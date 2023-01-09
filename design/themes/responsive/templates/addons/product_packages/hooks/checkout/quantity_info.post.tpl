{if $product.items_in_package && $product.items_in_package != 1}
    <div><span id="for_amount_{$key}" data-ca-box-contains="{$product.items_in_package}">{($product.amount/$product.items_in_package)|round:2}</span>&nbsp;{__('of_box')}</div>
{/if}
