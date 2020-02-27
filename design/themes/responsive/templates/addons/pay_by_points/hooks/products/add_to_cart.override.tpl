{if $product.is_pbp == 'Y'}
{$points_to_add = $product.price - ''|fn_get_available_points}
{if $points_to_add > 0}
  <span>{__("pay_by_points__not_enough_points", ['%points%' => $points_to_add])}</span>
{/if}
{/if}
