{include file="common/subheader.tpl" title=__("product_groups")}

<div class="control-group">
    <label for="elm_company_separate_zero_products" class="control-label">{__("separate_zero_products")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[separate_zero_products]" value="{"YesNo::NO"|enum}">
        <input type="checkbox" name="company_data[separate_zero_products]" id="elm_company_allow_order_cancellation" value="{"YesNo::YES"|enum}" {if $company_data.separate_zero_products == "YesNo::YES"|enum} checked="checked" {/if} />
    </div>
</div>
