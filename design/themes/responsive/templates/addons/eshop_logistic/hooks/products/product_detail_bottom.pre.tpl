{if $addons.eshop_logistic.eshop_widget_key}
    <div id="eShopLogisticApp" data-key="{$addons.eshop_logistic.eshop_widget_key}"></div>

    {if $product.weight == 0}
        {$weight = 000.1}
    {else} 
        {$weight = $product.weight}
    {/if}
    <a class="ty-btn" 
        data-widget-button
        data-allow-send=0
        data-article="{$product.product_code}" 
        data-name="{$product.product}" 
        data-price="{$product.price}" 
        data-weight="{$weight}"
        data-ip="{$auth.ip}">
        {__('eshop_logistic.calculate_shipping')}
    </a>
{/if}