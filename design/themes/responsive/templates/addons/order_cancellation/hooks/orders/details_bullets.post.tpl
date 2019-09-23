{if $order_info.allow_cancel}
    {include file="buttons/button.tpl" but_meta="ty-btn__text cm-post" but_role="text" but_text=__("cancel_order") but_href="orders.cancel?order_id=`$order_info.order_id`" but_icon="ty-orders__actions-icon ty-icon-cancel"}
{/if}