<span class="ty-float-left" id="wish_list_button_container_{$product.product_id}">
{$but_icon = 'ty-azure-icon-star-empty'}
{$wl_products = $smarty.session.wishlist.products|fn_array_column:'product_id'}
{$tooltip = __("add_to_wishlist")}
{if $product.product_id|in_array:$wl_products}
	{$but_icon = 'ty-azure-icon-star-full'}
	{$tooltip = __("product_in_wishlist")}
{/if}
{include file="buttons/button.tpl" but_id=$but_id but_meta="ty-btn__text ty-add-to-wish" but_name=$but_name but_icon=$but_icon but_text='' but_role="text" but_onclick=$but_onclick but_href=$but_href tooltip=$tooltip}
<!--wish_list_button_container_{$product.product_id}--></span>