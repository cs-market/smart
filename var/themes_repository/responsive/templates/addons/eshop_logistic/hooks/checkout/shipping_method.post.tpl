{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'eshop_logistic' }
    {if $addons.eshop_logistic.eshop_use_maps == "Y" && $addons.geo_maps.status == 'A'}
        {script src="js/addons/store_locator/pickup.js"}
    {/if}
    {$terminals = $cart['shippings_extra']['data']['eshop'][$group_key][$shipping.shipping_id]['terminals']}
    {$servise_info = "eshop_services_info"|fn_get_session_data}
    {$selected_payment = $cart['payment_method_data']['eshop_payment_type']|fn_eshop_logistic_get_eshop_payment_type_by_code}
    <div class="litecheckout__item">
        <div class='eshop_shipping_service_info'>
            {if $servise_info} 
                {foreach $servise_info item=eshop_service key=eshop_service_key}
                    {if strpos($shipping.service_code, $eshop_service_key) !== false}
                        {if !empty($eshop_service.comment)}
                            <div class='eshop_common_comment ty-checkout__shipping-tips'>{$eshop_service.comment nofilter}</div>
                        {/if}
                        {if !empty($eshop_service.payments)} 
                            {foreach $eshop_service.payments item=eshop_payment}
                                {if $eshop_payment.key == $selected_payment}
                                    <div class='eshop_payment_comment ty-checkout__shipping-tips'>{$eshop_payment.comment}</div>
                                {/if}
                            {/foreach}
                        {/if}
                    {/if}
                {/foreach}
            {/if}
            {if $cart['shippings_extra']['data']['eshop'][$group_key][$shipping.shipping_id]['comment']}
                <div class='eshop_shipping_comment ty-checkout__shipping-tips'>{$cart['shippings_extra']['data']['eshop'][$group_key][$shipping.shipping_id]['comment'] nofilter}</div>
            {/if}
            
        </div>
    </div>
    {if $terminals}
        
        {$selected_office = $product_groups[$group_key]['chosen_shippings']|current}
        <div class="litecheckout__group">
            <div class="litecheckout__item">
                <h2 class="litecheckout__step-title">{__("lite_checkout.select_pickup_item")}</h2>
            </div>
            <div class="litecheckout__item eshop_terminals_content">
                {if $addons.eshop_logistic.eshop_use_maps == "Y" && $addons.geo_maps.status == 'A'}
                    {if empty($selected_office.office_id)}
                        {foreach from=$terminals item=arrival_terminal name="eshop_termianls_each_find_selected"} 
                            {$selected_terminal_id = $arrival_terminal.code}
                            {break}
                        {/foreach}
                    {else}
                        {$selected_terminal_id = $selected_office.office_id}
                    {/if}
                    
                    {include file="addons/eshop_logistic/views/checkout/components/shippings/map_and_list_eshop_logistic.tpl"}
                {else}
                    {foreach from=$terminals item=arrival_terminal name="eshop_termianls_each"}
                        <div class="ty-eshop-one-terminal">
                            <input  type="radio" id="eshop_service_terminal_{$arrival_terminal.code}_{$shipping.shipping_id}" name="eshop_service_terminal[{$group_key}][{$shipping.shipping_id}]" 
                                    value="{$arrival_terminal.code}" 
                                    {if $selected_office.office_id == $arrival_terminal.code || empty($selected_office.office_id) && $smarty.foreach.eshop_termianls_each.first}checked="checked"{/if} 
                                    id="office_{$arrival_terminal.code}"
                                    onchange="fn_calculate_total_shipping_cost(true)" 
                                    class="ty-eshop-terminal-radio" />
                            <div class="ty-eshop-terminal__label">
                                <label for="eshop_service_terminal_{$arrival_terminal.code}_{$shipping.shipping_id}" >
                                    <p class="ty-eshop-one-terminal__name">{$arrival_terminal.address}</p>
                                    <div class="ty-eshop-one-terminal__description">
                                        {$arrival_terminal.note}
                                    </div>
                                    <div class="ty-eshop-one-terminal__phone">
                                        {$arrival_terminal.phone}
                                    </div>
                                    <div class="ty-eshop-one-terminal__worktime">
                                        {$arrival_terminal.workTime}
                                    </div>
                                </label>
                            </div>
                            </label>
                        </div>
                    {/foreach}
                {/if}
            </div>
        </div>
    {/if}
   
{/if}
