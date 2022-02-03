{if !$hide_wishlist_button}
    <span id="wish_list_{$obj_prefix}{$product.product_id}">
    {include file="addons/wishlist/views/wishlist/components/add_to_wishlist.tpl"
        wishlist_but_id="button_wishlist_`$obj_prefix``$product.product_id`"
        wishlist_but_name="dispatch[wishlist.add..`$product.product_id`]"
        wishlist_but_role="text"
        wishlist_but_meta = 'ty-btn-icon ty-btn__add-to-wish cm-ajax-full-render'
    }
    <!--wish_list_{$obj_prefix}{$product.product_id}--></span>
{/if}
