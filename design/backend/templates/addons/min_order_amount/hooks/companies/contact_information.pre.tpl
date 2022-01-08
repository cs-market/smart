<div class="control-group">
    <label class="control-label" for="elm_company_min_order_amount">{__("min_order_amount")}:</label>
    <div class="controls">
        <input type="text" name="company_data[min_order_amount]" id="elm_company_min_order_amount" value="{$company_data.min_order_amount}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="elm_company_allow_additional_ordering">{__("allow_additional_ordering")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[allow_additional_ordering]" value="N">
        <input type="checkbox" name="company_data[allow_additional_ordering]" id="elm_company_allow_additional_ordering" value="Y" {if $company_data.allow_additional_ordering == 'Y'}checked="checked"{/if}>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_company_min_order_weight">{__("min_order_weight")}:</label>
    <div class="controls">
        <input type="text" name="company_data[min_order_weight]" id="elm_company_min_order_weight" value="{$company_data.min_order_weight}" />
    </div>
</div>
