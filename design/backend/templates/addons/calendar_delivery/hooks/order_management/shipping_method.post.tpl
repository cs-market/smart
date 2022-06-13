{$chosen_shipping_id = $cart.chosen_shipping.0}
{$chosen_shipping = $cart.shipping.$chosen_shipping_id}
{if $chosen_shipping.module == 'calendar_delivery'}

    <div class="control-group">
        <label class="control-label" for="elm_order_delivery_date">{__("delivery_date")}:</label>
        <div class="controls">
            {include file="common/calendar.tpl" date_id="elm_order_delivery_date" date_name="delivery_date[{$group_key}]" date_val=$cart.delivery_date|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_order_delivery_period">{__("calendar_delivery.delivery_period")}:</label>
        <div class="controls">
            {$c_data = $group.company_id|fn_get_company_data}
            {$periods = $c_data.period_start|fn_get_calendar_delivery_period:$c_data.period_finish:$c_data.period_step}

            <select name="delivery_period[{$group_key}]" id="elm_order_delivery_period">
            {foreach from=$periods item="period"}
            <option value="{$period.value}" {if $period.value == $cart.delivery_period}selected="selected"{/if}>{$period.value}</option>
            {/foreach}
            </select>
        </div>
    </div>
{/if}
