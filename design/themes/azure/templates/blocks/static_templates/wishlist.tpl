<div id="wish_list_{$block.snapping_id}">
<a href="{"wishlist.view"|fn_url}" class="ty-top-menu__item azure-indicator-container" rel="nofollow"><i class="ty-azure-icon-wishlist"></i>
{if $smarty.session.wishlist.products}
<div class='azure-count-indication'>{$smarty.session.wishlist.products|count}</div>
{/if}
</a>1
<!--wish_list_{$block.snapping_id}--></div>