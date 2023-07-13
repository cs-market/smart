{if $product.promo.view_separate == "YesNo::YES"|enum}
    {include file="buttons/button.tpl" but_text=__("buy_cheaper") but_href="promotions.view&promotion_id=`$product.promo.promotion_id`" but_meta='ty-btn__primary ty-btn__buy-cheaper'}
    {$but_text = ' ' scope="parent"}
    {$active_class = '' scope="parent"}
{/if}
