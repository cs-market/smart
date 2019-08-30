{if !$show_product_options && $product.selected_options}
{foreach name="product_options" from=$product.selected_options item="product_option" key="product_option_id"}
    <input type="hidden" name="product_data[{$product.product_id}][product_options][{$product_option_id}]" value="{$product_option}">
{/foreach}
{/if}