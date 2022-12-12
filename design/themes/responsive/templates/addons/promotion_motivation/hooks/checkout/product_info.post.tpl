{if $product.participates_in_promo}
    {if $product.participates_in_promo.view_separate = "YesNo::YES"|enum}
        {$promotion_link = "promotions.view&promotion_id=`$product.participates_in_promo.promotion_id`"|fn_url}
    {else}
        {$promotion_link = "promotions.list"|fn_url}
    {/if}
    <p class="ty-cart-content__participates-in-promo">{__('promotion_motivation.participates_in_promo', ['[promotion]' => $product.participates_in_promo.name, '[promotion_link]' => $promotion_link])}</p>
{/if}
