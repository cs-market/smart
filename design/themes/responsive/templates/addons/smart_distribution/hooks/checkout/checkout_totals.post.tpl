{$weight = $cart.weight|default:$cart.product_groups.0.package_info_full.packages.0.weight}
{if $weight}
<li class="ty-cart-statistic__item ty-statistic-list-subtotal">
    <span class="ty-cart-statistic__title">{__("products_weight")}</span>
    <span class="ty-cart-statistic__value">{$weight} ({$settings.General.weight_symbol nofilter})</span>
</li>
{/if}
