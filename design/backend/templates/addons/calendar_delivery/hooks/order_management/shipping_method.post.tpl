{$chosen_shipping_id = $cart.chosen_shipping.0}
{$chosen_shipping = $cart.shipping.$chosen_shipping_id}
{if $chosen_shipping.module == 'calendar_delivery'}

    <div class="control-group">
        <label class="control-label" for="elm_order_delivery_date">{__("delivery_date")}:</label>
        <div class="controls">
            {include file="common/calendar.tpl" date_id="elm_order_delivery_date" date_name="delivery_date" date_val=$cart.delivery_date|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
        </div>
    </div>
{/if}