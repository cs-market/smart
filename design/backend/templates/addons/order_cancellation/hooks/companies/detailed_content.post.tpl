{include file="common/subheader.tpl" title=__("order_cancellation")}

<div class="control-group">
    <label for="elm_company_allow_order_cancellation" class="control-label">{__("allow_order_cancellation")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[allow_order_cancellation]" value="{"YesNo::NO"|enum}">
        <input type="checkbox" name="company_data[allow_order_cancellation]" id="elm_company_allow_order_cancellation" value="{"YesNo::YES"|enum}" {if $company_data.allow_order_cancellation == "YesNo::YES"|enum} checked="checked" {/if} />
    </div>
</div>
