{include file="common/subheader.tpl" title=__("product_availability")}

<div class="control-group">
    <label class="control-label" for="elm_company_tracking">{__("track_inventory")}:</label>
    <div class="controls">
        <select name="company_data[tracking]" id="elm_company_tracking">
            <option value="{"ProductTracking::TRACK"|enum}" {if $company_data.tracking == "ProductTracking::TRACK"|enum} selected="selected" {/if}>{__("yes")}</option>
            <option value="{"ProductTracking::DO_NOT_TRACK"|enum}" {if $company_data.tracking == "ProductTracking::DO_NOT_TRACK"|enum} selected="selected" {/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_out_of_stock_actions">{__("out_of_stock_actions")}:</label>
    <div class="controls">
        <select class="span3" name="company_data[out_of_stock_actions]" id="elm_company_out_of_stock_actions">
            <option value="N" {if $company_data.out_of_stock_actions == "N"}selected="selected"{/if}>{__("text_out_of_stock")}</option>
            <option value="B" {if $company_data.out_of_stock_actions == "B"}selected="selected"{/if}>{__("buy_in_advance")}</option>
            <option value="S" {if $company_data.out_of_stock_actions == "S"}selected="selected"{/if}>{__("sign_up_for_notification")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_show_out_of_stock_product">{__("show_out_of_stock_product")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="company_data[show_out_of_stock_product]" value="{"YesNo::NO"|enum}" />
            <input type="checkbox" name="company_data[show_out_of_stock_product]" id="elm_company_show_out_of_stock_product" value="{"YesNo::YES"|enum}" {if $company_data.show_out_of_stock_product == "YesNo::YES"|enum} checked="checked"{/if}>
        </label>
    </div>
</div>

