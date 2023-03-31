{if $product.reward_points_mechanics == "RewardPointsMechanics::FULL_PAYMENT"|enum && $product.is_pbp == "YesNo::YES"|enum}
    {$points_to_add = $product.point_price - ''|fn_get_available_points}
    {if $points_to_add > 0}
        <span>{__("extended_reward_points.not_enough_points", [$points_to_add])}</span>
    {/if}
{/if}
