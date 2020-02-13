{if $user_info.points && $product.is_pbp == 'Y'}
  {if $cart.products[$key].extra.pay_by_points.use_bonus_pay}
    <span>
      {__("payed_by_points")}:
      {$cart.products[$key].extra.pay_by_points.product_cart_point_price}
    </span>
  {else}
  {* FIXME: use $user_info.points - cart points_info *}
    {if $product.subtotal - $user_info.points > 0}
      <span>{__("pay_by_points__not_enough_points", ['%points%' => $product.subtotal - $user_info.points])}</span>
    {else}
        {include file="buttons/button.tpl" but_href="checkout.point_payment_product?cart_id=`$key`" but_meta="cm-ajax cm-ajax-full-render" but_target_id="cart_status*" but_role="act" but_text=__("pay_by_points") but_name="delete_cart_item"}
    {/if}
  {/if}
{/if}
