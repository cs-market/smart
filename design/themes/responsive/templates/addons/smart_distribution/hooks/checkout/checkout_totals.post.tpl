{if $cart.product_groups.0.package_info_full.packages.0.weight}
<li class="ty-cart-statistic__item ty-statistic-list-subtotal">
    <span class="ty-cart-statistic__title">{__("products_weight")}</span>
    <span class="ty-cart-statistic__value">{$cart.product_groups.0.package_info_full.packages.0.weight} ({$settings.General.weight_symbol nofilter})</span>
</li>
{/if}
