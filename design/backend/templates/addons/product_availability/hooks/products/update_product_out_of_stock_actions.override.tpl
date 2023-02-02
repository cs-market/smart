<div class="control-group">
    <label class="control-label" for="elm_date_avail_till_holder">{__("available_till")}:</label>
    <div class="controls">
        {include file="common/calendar.tpl" date_id="elm_date_avail_till_holder" date_name="product_data[avail_till]" date_val=$product_data.avail_till|default:"" start_year=$settings.Company.company_start_year}
    </div>
</div>

{hook name="products:update_product_out_of_stock_actions"}
<div class="control-group">
    <label class="control-label" for="elm_out_of_stock_actions">{__("out_of_stock_actions")}:</label>
    <div class="controls">
        <select class="span3" name="product_data[out_of_stock_actions]" id="elm_out_of_stock_actions">
            <option value="N" {if $product_data.out_of_stock_actions == "N"}selected="selected"{/if}>{__("text_out_of_stock")}</option>
            <option value="B" {if $product_data.out_of_stock_actions == "B"}selected="selected"{/if}>{__("buy_in_advance")}</option>
            <option value="S" {if $product_data.out_of_stock_actions == "S"}selected="selected"{/if}>{__("sign_up_for_notification")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_show_out_of_stock_product">{__("show_out_of_stock_product")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="product_data[show_out_of_stock_product]" value="{"YesNo::NO"|enum}" />
            <input type="checkbox" name="product_data[show_out_of_stock_product]" id="elm_show_out_of_stock_product" value="{"YesNo::YES"|enum}" {if $product_data.show_out_of_stock_product == "YesNo::YES"|enum} checked="checked"{/if}>
        </label>
    </div>
</div>
{/hook}
