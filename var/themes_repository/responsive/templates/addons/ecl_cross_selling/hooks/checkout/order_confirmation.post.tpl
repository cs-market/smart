{if $addons.ecl_cross_selling.add_to_cart_notification == 'Y' && !empty($related_products_for_cart)}
    <div class="hidden cm-dialog-auto-open cm-dialog-auto-size" id="related_products_block" title="{__('anything_else_maybe')}">
        <div class="related-product-reminder">
            {include file="addons/ecl_cross_selling/components/products_scroller.tpl" items=$related_products_for_cart block=$block_related_product_data}
        </div>
    </div>
{/if}