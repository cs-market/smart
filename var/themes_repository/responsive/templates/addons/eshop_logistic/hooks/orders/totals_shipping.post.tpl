{foreach from=$order_info.shipping item="shippin"}
    {if ($shipping.module == 'eshop_logistic') && ($shipping.data.terminals) && ($shipping.office_id)}
        {foreach $shipping.data.terminals as $eshop_terminal_data}
            {if $eshop_terminal_data.code == $shipping.office_id}
                <div>
                    <b>{__("eshop_logistic.shipping.header_shipping_terminal")}</b>
                    <p class="strong">{$eshop_terminal_data.code}</p>

                    <p class="muted">
                        {$eshop_terminal_data.address}
                    </p>
                </div>
            {/if}
        {/foreach}
    {/if}
{/foreach}