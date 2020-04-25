{hook name="products:list_quantity"}
    {if $product.tracking == "ProductTracking::TRACK_WITH_OPTIONS"|enum}
	{include file="buttons/button.tpl" but_text=__("edit") but_href="product_options.inventory?product_id=`$product.product_id`" but_role="edit"}
    {else}
	<input type="text" name="products_data[{$product.product_id}][amount]" size="6" value="{$product.amount}" class="input-mini input-hidden" />
    {/if}
{/hook}