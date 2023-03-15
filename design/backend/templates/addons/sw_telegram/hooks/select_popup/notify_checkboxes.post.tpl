{if $addons.sw_telegram.tg_order_status_notification == 'Y' && $notify && ($orders || $order_info)}
    <li><a><label for="{$prefix}_{$id}_notify_telegram">
        <input type="checkbox" name="__notify_telegram" id="{$prefix}_{$id}_notify_telegram" value="Y" {if $addons.sw_telegram.tg_order_status_notification == true} checked="checked" {/if} onclick="Tygh.$('input[name=__notify_telegram]').prop('checked', this.checked);" />
        {__("sw_telegram.notify_telegram")}</label></a>
    </li>
{/if}