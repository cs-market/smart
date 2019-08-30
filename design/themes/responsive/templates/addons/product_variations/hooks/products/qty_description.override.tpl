{if $product.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_CONFIGURABLE"|constant}
    <div class="cm-reload-{$obj_prefix}{$obj_id}" id="qty_description_{$obj_prefix}{$obj_id}">
        {if $min_qty && $product.min_qty}
            <p class="ty-min-qty-description">{__("text_cart_min_qty", ["[product]" => $product.product, "[quantity]" => $product.min_qty])}.</p>
        {/if}
    <!--qty_description_{$obj_prefix}{$obj_id}--></div>
{/if}