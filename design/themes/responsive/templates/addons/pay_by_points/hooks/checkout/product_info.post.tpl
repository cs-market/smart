{if $user_info.points && $product.is_pbp == 'Y'}

  {if $product.subtotal - $user_info.points > 0}
    <span>{__("pay_by_points__not_enough_points", ['%points%' => $product.subtotal - $user_info.points])}</span>
  {else}
button
  {/if}
{* /* {"test"|fn_print_r:$product.is_pbp} */ *}
{* /* {"test"|fn_print_r:$product.subtotal} */ *}
{* /* {"test"|fn_print_r:$user_info.points} */ *}
{/if}
