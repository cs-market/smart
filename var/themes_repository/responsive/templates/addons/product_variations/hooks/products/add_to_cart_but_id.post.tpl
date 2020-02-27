{if $product.variation_product_id}
    {$_but_id = "button_cart_`$obj_prefix``$obj_id`_`$product.variation_product_id`" scope=parent}
{else}
    {$_but_id = "button_cart_`$obj_prefix``$obj_id`" scope=parent}
{/if}