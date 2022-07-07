{* for baltika*}
{if $auth.company_id==45}
{hook name="products:product_labels"}
{if $show_shipping_label && $product.free_shipping == "Y"}
    {include
        file="views/products/components/product_label.tpl"
        label_meta="ty-product-labels__item--shipping"
        label_text=__("free_shipping")
        label_mini=$product_labels_mini
        label_static=$product_labels_static
        label_rounded=$product_labels_rounded
    }
{/if}
{if $show_discount_label && ($product.discount_prc || $product.list_discount_prc) && $show_price_values}
    {if $product.discount}
        {$label_text = "-{$product.discount_prc}%"}
    {else}
        {$label_text = "-{$product.list_discount_prc}%"}
    {/if}

    {include
        file="views/products/components/product_label.tpl"
        label_meta="ty-product-labels__item--discount"
        label_text=$label_text
        label_mini=$product_labels_mini
        label_static=$product_labels_static
        label_rounded=$product_labels_rounded
    }
{/if}
{/hook}
{/if}
