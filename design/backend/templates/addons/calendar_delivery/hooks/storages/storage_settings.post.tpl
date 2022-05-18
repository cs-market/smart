{include file="common/subheader.tpl" title=__("calendar_delivery")}

<div class="control-group">
    <label for="elm_company_nearest_delivery" class="control-label">{__("calendar_delivery.nearest_delivery")}:</label>
    <div class="controls">
        <label class="radio inline" for="storage_data_nearest_delivery_today"><input type="radio" name="storage_data[nearest_delivery]" id="storage_data_nearest_delivery_today" {if $storage.nearest_delivery == '0'}checked="checked"{/if} value="0">{__('today')}</label>
        <label class="radio inline" for="storage_data_nearest_delivery_tomorrow"><input type="radio" name="storage_data[nearest_delivery]" id="storage_data_nearest_delivery_tomorrow" {if $storage.nearest_delivery == '1'}checked="checked"{/if} value="1">{__('tomorrow')}</label>
        <label class="radio inline" for="storage_data_nearest_delivery_aftertomorrow"><input type="radio" name="storage_data[nearest_delivery]" id="storage_data_nearest_delivery_aftertomorrow" {if $storage.nearest_delivery == '2'}checked="checked"{/if} value="2">{__('after_tomorrow')}</label>
    </div>
</div>

<div class="control-group">
    <label for="elm_company_working_time_till" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="__('working_time_till_error_message')">{__("calendar_delivery.working_time_till")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_company_working_time_till" size="5" maxlength="5" type="text" name="storage_data[working_time_till]" value="{$storage.working_time_till}" placeholder="00:00" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_saturday_shipping" class="control-label">{__("calendar_delivery.saturday_shipping")}:</label>
    <div class="controls">
        <input type="hidden" name="storage_data[saturday_shipping]" value="N">
        <input type="checkbox" name="storage_data[saturday_shipping]" id="elm_company_saturday_shipping" value="Y" {if $storage.saturday_shipping == 'Y'} checked="checked" {/if} />
    </div>
</div>
<div class="control-group">
    <label for="elm_company_sunday_shipping" class="control-label">{__("calendar_delivery.sunday_shipping")}:</label>
    <div class="controls">
        <input type="hidden" name="storage_data[sunday_shipping]" value="N">
        <input type="checkbox" name="storage_data[sunday_shipping]" id="elm_company_sunday_shipping" value="Y" {if $storage.sunday_shipping == 'Y'} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_monday_rule" class="control-label">{__("calendar_delivery.monday_rule")}:</label>
    <div class="controls">
        <input type="hidden" name="storage_data[monday_rule]" value="N">
        <input type="checkbox" name="storage_data[monday_rule]" id="elm_company_monday_rule" value="Y" {if $storage.monday_rule == 'Y'} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_period_start" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="{__('period_start_error_message')}">{__("calendar_delivery.period_start")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_company_period_start" size="5" maxlength="5" type="text" name="storage_data[period_start]" value="{$storage.period_start}" placeholder="00:00" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_period_finish" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="{__('period_finish_error_message')}">{__("calendar_delivery.period_finish")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_company_period_finish" size="5" maxlength="5" type="text" name="storage_data[period_finish]" value="{$storage.period_finish}" placeholder="00:00" />
    </div>
</div>

<div class="control-group">
    <label for="elm_company_period_step" class="control-label">{__("calendar_delivery.period_step")}:</label>
    <div class="controls">
        <input class="cm-trim" id="elm_company_period_step" size="2" maxlength="2" type="text" name="storage_data[period_step]" value="{$storage.period_step}" placeholder="2" />
    </div>
</div>
