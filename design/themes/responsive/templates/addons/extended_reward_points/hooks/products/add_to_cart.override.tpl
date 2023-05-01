{if $auth.extended_reward_points.reward_points_mechanics == "RewardPointsMechanics::FULL_PAYMENT"|enum && $product.is_pbp == "YesNo::YES"|enum}
    {$in_use = ''|fn_get_cart_points_in_use}
    {$points_to_add = $product.point_price + $in_use - $auth.points}
    {if $points_to_add > 0}
        <span>{__("extended_reward_points.not_enough_points", [$points_to_add])}</span>
    {/if}
{/if}
