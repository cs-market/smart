{include file="common/subheader.tpl" title=__("calendar_delivery")}

<div class="control-group">
    <label for="elm_company_tomorrow_rule" class="control-label">{__("calendar_delivery.tomorrow_rule")}:</label>
    <div class="controls">
    	<input type="hidden" name="company_data[tomorrow_rule]" value="N">
        <input type="checkbox" name="company_data[tomorrow_rule]" id="elm_company_tomorrow_rule" value="Y" {if $company_data.tomorrow_rule == 'Y'} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_tomorrow_timeslot" class="control-label cm-regexp" data-ca-regexp="^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$" data-ca-message="__('tomorrow_timeslot_error_message')">{__("calendar_delivery.tomorrow_timeslot")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_company_tomorrow_timeslot" size="5" maxlength="5" type="text" name="company_data[tomorrow_timeslot]" value="{$company_data.tomorrow_timeslot}" placeholder="00:00" />
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