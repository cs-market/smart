{if $product.weight}
    <div class="ty-cart-content__weight ty-weight" id="weight_{$key}">
        {__("weight")}: <span class="cm-reload-{$obj_id}" id="product_weight_update_{$obj_id}">{$product.weight*$product.amount} ({$settings.General.weight_symbol nofilter})<!--product_weight_update_{$obj_id}--></span>
    </div>
{/if}
