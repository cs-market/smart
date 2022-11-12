{** block-description:description **}
<div class="ty-column2">
    <h3 class="tab-list-title" id="{$tab.html_id}">{__('about_product')}</h3>
{if $product.full_description}
    <div {live_edit name="product:full_description:{$product.product_id}"}>{$product.full_description nofilter}</div>
{else if $product.short_description}
    <div {live_edit name="product:short_description:{$product.product_id}"}>{$product.short_description nofilter}</div>
{/if}
</div>
<div class="ty-column2">
    <h3 class="tab-list-title" id="{$tab.html_id}">{__('features')}</h3>
    {include file="views/products/components/product_features.tpl" product_features=$product.product_features details_page=true}
</div>
