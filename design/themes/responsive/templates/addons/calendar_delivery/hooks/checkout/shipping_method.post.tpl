{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'calendar_delivery'}
<div class="litecheckout__container">
    <div class="litecheckout__item">
        <p>{__("delivery_date")}&nbsp;</p>

        {$min_date = "+{$shipping.service_params.nearest_delivery_day}"}
        {$default = "+{$shipping.service_params.nearest_delivery_day} day"|strtotime}

        {include file="addons/calendar_delivery/components/calendar.tpl" date_id="delivery_date_`$group_key`_`$shipping.shipping_id`" date_name="delivery_date[`$group_key`]" date_val = $group.delivery_date|default:$default|fn_parse_date min_date=$min_date max_date="+30" service_params=$shipping.service_params extra='readonly="_readonly"'}
        {include
            file="addons/calendar_delivery/components/period.tpl"
            date_id="delivery_period_`$group_key`_`$shipping.shipping_id`"
            datapicker_id="delivery_date_`$group_key`_`$shipping.shipping_id`"
            date_name="delivery_period[`$group_key`]"
            date_val = $cart.delivery_period.$group_key
            period_start = $shipping.service_params.period_start
            period_finish = $shipping.service_params.period_finish
            period_step = $shipping.service_params.period_step
        }
    </div>
    {if $shipping.service_params.offer_documents == "YesNo::YES"|enum}
    <div class="litecheckout__item">
        <div class="litecheckout__terms" id="litecheckout_terms">
            <div class="ty-control-group ty-checkout__terms">
                <div class="cm-field-container">
                    <input type="hidden" name="documents_originals[{$group_key}]" value="N">
                    <label for="documents_originals_{$group_key}" class="cm-check-agreement"><input type="checkbox" id="documents_originals_{$group_key}" name="documents_originals[{$group_key}]" value="Y" class="checkbox" {if $shipping.service_params.offer_documents_checked == "YesNo::YES"|enum}checked="_checked"{/if}>{__('calendar_delivery.documents_originals')}</label>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>
{/if}
