<div id="cart_promotion_motivation_wrapper">
    {if $cart.promotion_motivation}
    <div class="ty-promotion-motivation">
        <div class="ty-promotion-motivation__title">
            {$cart.promotion_motivation.title nofilter}
        </div>
        <div class="ty-promotion-motivation__body">
            {$cart.promotion_motivation.body nofilter}
        </div>
        <div class="ty-promotion-motivation__button clearfix">
            {include file="buttons/button.tpl" but_text=__('move_to_catalog') but_href=""|fn_url but_meta="ty-btn__primary ty-float-right"}
        </div>
    </div>
    {/if}
<!--cart_promotion_motivation_wrapper--></div>
