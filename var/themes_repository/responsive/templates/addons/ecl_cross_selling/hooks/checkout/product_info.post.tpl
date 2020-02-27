{if !empty($cart_related_product_data.$key.products)}
    <div class="related-product-checkout">
        <strong>{__("you_may_also_like")}</strong>
        {include file="addons/ecl_cross_selling/components/products_scroller.tpl" items=$cart_related_product_data.$key.products block=$cart_related_product_data.$key.block_data image_width_related_product=90 related_product_class='cart-related-block'}
    </div>
{/if}
