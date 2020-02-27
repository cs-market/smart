{if $user_info.points && $product.is_pbp == 'Y'}
  <input type="hidden" name="cart_products[{$key}][pay_by_points]" value="Y">
  {if $cart.products[$key].extra.pay_by_points.use_bonus_pay}
    <span>
      {__("payed_by_points")}:
      {$cart.products[$key].extra.pay_by_points.product_cart_point_price}
    </span>
  {/if}
{/if}
