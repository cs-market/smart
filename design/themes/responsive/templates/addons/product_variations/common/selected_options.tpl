{foreach from=$product.selected_options key=option_id item=option_value}
    <input type="hidden" name="product_data[{$product.product_id}][product_options][{$option_id}]" value="{$option_value}" />
{/foreach}
