{if $ul.action == $smarty.const.CHANGE_DUE_SUBTRACT}
{assign var="reason" value=$ul.reason|unserialize}
{if $reason}
    {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
    {__("order")}&nbsp;{if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<span>#{$reason.order_id}</span>{if $order_exist}</a>{/if}:&nbsp;{$reason.text}
{else}
    {$ul.reason}
{/if}
{/if}
