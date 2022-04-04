{foreach from=$cart.shipping item=dellin_shipping}
    {if $dellin_shipping.module == 'eshop_logistic'}
        {if $product_groups}
            {foreach from=$product_groups key=group_key item=group}
                {if $group.shippings && !$group.shipping_no_required}
                    {foreach from=$group.shippings item=shipping}
                        {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}

                            {$terminals = $cart['shippings_extra']['data']['eshop'][$group_key][$shipping.shipping_id]['terminals']}

                            {if $terminals}
                                
                                {$selected_office =  $product_groups[$group_key]['chosen_shippings'][$group_key]['office_id']}
                                
                                {foreach from=$terminals item=arrival_terminal name="eshop_termianls_each"}
                                    <div class="sidebar-row">
                                        <div class="control-group">
                                            <div class="controls">
                                                <input  type="radio" name="eshop_service_terminal[{$group_key}][{$shipping.shipping_id}]" 
                                                    value="{$arrival_terminal.code}" 
                                                    {if $selected_office == $arrival_terminal.code || empty($selected_office) && $smarty.foreach.eshop_termianls_each.first}checked="checked"{/if} 
                                                    id="office_{$arrival_terminal.code}"
                                                    onchange="fn_calculate_total_shipping_cost(true)" 
                                                    class="" />
                                                {$arrival_terminal.code}
                                                <p class="muted">
                                                    {$arrival_terminal.address}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        {/if}
                    {/foreach}
                {/if}
            {/foreach}
        {/if}
    {/if}
{/foreach}
