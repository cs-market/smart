{if $product.items_in_package}{$qty_step = $product.items_in_package}{else}{$qty_step = $product.qty_step}{/if}
{if $qty_step > 1}
    <div class="box-price muted">
        {include file="common/price.tpl" value=$product.price * $qty_step}<span class="box-price__suffix"> {__('per_box')}</span>
    </div>
{/if}
