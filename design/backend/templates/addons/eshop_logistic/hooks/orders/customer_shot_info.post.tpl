{foreach from=$order_info.shipping item="shipping" key="shipping_id"}
    
    {if ($shipping.module == 'eshop_logistic') && ($shipping.data.terminals) && ($shipping.office_id)}
        {foreach $shipping.data.terminals as $eshop_terminal_data}
            {if $eshop_terminal_data.code == $shipping.office_id}
                <div class="well orders-right-pane form-horizontal">
                    <div class="control-group shift-top">
                        <div class="control-label">
                            {include file="common/subheader.tpl" title=__("eshop_logistic.shipping.header_shipping_terminal")}
                        </div>
                    </div>

                    <p class="strong">{$eshop_terminal_data.code}</p>

                    <p class="muted">
                        {$eshop_terminal_data.address}
                    </p>
                </div>
                {break}
            {/if}
        {/foreach}
        
    {/if}
{/foreach}
