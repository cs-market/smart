{if $product.box_contains}{$qty_step = $product.box_contains}{else}{$qty_step = $product.qty_step}{/if}
{if $qty_step > 1}
    <div class="box-price muted">
        {include file="common/price.tpl" value=$product.price * $qty_step} {__('per_box')}
    </div>
{/if}
