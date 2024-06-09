{if $auth.extended_reward_points.reward_points_mechanics == "RewardPointsMechanics::FULL_PAYMENT"|enum && $product.is_pbf == "YesNo::YES"|enum}
    {include file="buttons/button.tpl" but_id=$but_id but_text=__("extended_reward_points.pay_by_points") but_name="dispatch[checkout.add.points_pay.`$obj_id`]" but_onclick=$but_onclick but_href=$but_href but_target=$but_target but_role=$but_role|default:"text" but_meta="ty-btn__primary ty-btn__add-to-cart cm-form-dialog-closer `$active_class` ty-btn__add-to-cart__by-points"}
{/if}
