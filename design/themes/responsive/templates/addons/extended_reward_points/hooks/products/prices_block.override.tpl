{if $product.reward_points_mechanics == "RewardPointsMechanics::FULL_PAYMENT"|enum && $product.is_pbp == "YesNo::YES"|enum}
    <span class="ty-price{if !$product.point_price|floatval && !$product.zero_price_action} hidden{/if}" id="line_discounted_price_{$obj_prefix}{$obj_id}"><span class="ty-price-num">{__("points_lowercase", [$product.point_price])}</span></span>
{/if}
