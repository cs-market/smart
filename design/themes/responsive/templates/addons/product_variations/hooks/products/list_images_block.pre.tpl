{if $product_link
    && $product.options_changed
    && $product.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_CONFIGURABLE"|constant
}
    {$product_link=fn_url("products.view?product_id=`$product.product_id`&combination=`$product.options_combination`") scope="parent"}
{/if}
