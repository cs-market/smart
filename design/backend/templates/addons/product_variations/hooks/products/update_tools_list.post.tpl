{if $product_data.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_SIMPLE"|constant}
    <li>{btn type="list" data=["data-ca-confirm-text" => __("product_variations.confirm_convert_to_configurable_product")] text=__("product_variations.convert_to_configurable_product") href="product_variations.convert?product_id=`$id`" class="cm-confirm" method="POST"}</li>
{/if}
{if $product_data.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_CONFIGURABLE"|constant}
    <li>{btn type="list" text=__("product_variations.variations") href="products.manage?parent_product_id=`$id`&product_type={"\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_VARIATION"|constant}"}</li>
{/if}
