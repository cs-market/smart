{include file="common/subheader.tpl" title=__("calendar_delivery")}

<div class="control-group">
    <label for="elm_company_after17rule" class="control-label">{__("calendar_delivery.after17rule")}:</label>
    <div class="controls">
    	<input type="hidden" name="company_data[after17rule]" value="N">
        <input type="checkbox" name="company_data[after17rule]" id="elm_company_after17rule" value="Y" {if $company_data.after17rule == 'Y'} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_sunday_shipping" class="control-label">{__("calendar_delivery.sunday_shipping")}:</label>
    <div class="controls">
    	<input type="hidden" name="company_data[sunday_shipping]" value="N">
        <input type="checkbox" name="company_data[sunday_shipping]" id="elm_company_sunday_shipping" value="Y" {if $company_data.sunday_shipping == 'Y'} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_saturday_rule" class="control-label">{__("calendar_delivery.saturday_rule")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[saturday_rule]" value="N">
        <input type="checkbox" name="company_data[saturday_rule]" id="elm_company_saturday_rule" value="Y" {if $company_data.saturday_rule == 'Y'} checked="checked" {/if} />
    </div>
</div>