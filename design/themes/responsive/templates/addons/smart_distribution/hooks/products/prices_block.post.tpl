{if $product.qty_step > 1}
	<div class="box-price muted">
		{include file="common/price.tpl" value=$product.price * $product.qty_step} {__('per_box')}
	</div>
{/if}