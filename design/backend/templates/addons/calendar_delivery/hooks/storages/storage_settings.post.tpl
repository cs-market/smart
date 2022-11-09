{include file="common/subheader.tpl" title=__("calendar_delivery")}

<div class="control-group">
    <label for="elm_storage_nearest_delivery" class="control-label">{__("calendar_delivery.nearest_delivery")}:</label>
    <div class="controls">
        <label class="radio inline" for="storage_data_nearest_delivery_today"><input type="radio" name="storage_data[nearest_delivery]" id="storage_data_nearest_delivery_today" {if $storage.nearest_delivery == '0'}checked="checked"{/if} value="0">{__('today')}</label>
        <label class="radio inline" for="storage_data_nearest_delivery_tomorrow"><input type="radio" name="storage_data[nearest_delivery]" id="storage_data_nearest_delivery_tomorrow" {if $storage.nearest_delivery == '1' || !$storage}checked="checked"{/if} value="1">{__('tomorrow')}</label>
        <label class="radio inline" for="storage_data_nearest_delivery_aftertomorrow"><input type="radio" name="storage_data[nearest_delivery]" id="storage_data_nearest_delivery_aftertomorrow" {if $storage.nearest_delivery == '2'}checked="checked"{/if} value="2">{__('after_tomorrow')}</label>
    </div>
</div>

<div class="control-group">
    <label for="elm_storage_working_time_till" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="__('working_time_till_error_message')">{__("calendar_delivery.working_time_till")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_storage_working_time_till" size="5" maxlength="5" type="text" name="storage_data[working_time_till]" value="{$storage.working_time_till}" placeholder="00:00" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_storage_exception_days">{__("exception_days")}</label>
    <div class="controls">
        {include file="addons/calendar_delivery/components/weekdays_table.tpl" name="storage_data[exception_days]" value=$storage.exception_days}
    </div>
</div>

<div class="control-group">
    <label for="elm_storage_exception_time_till" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="__('working_time_till_error_message')">{__("calendar_delivery.exception_time_till")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_storage_exception_time_till" size="5" maxlength="5" type="text" name="storage_data[exception_time_till]" value="{$storage.exception_time_till}" placeholder="00:00" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_delivery_date">{__("delivery_date")}</label>
    <div class="controls">
        {include file="addons/calendar_delivery/components/weekdays_table.tpl" name="storage_data[delivery_date]" value=$storage.delivery_date|default:"1111111"}
    </div>
</div>

<div class="control-group">
    <label for="elm_storage_monday_rule" class="control-label">{__("calendar_delivery.monday_rule")}:</label>
    <div class="controls">
        <input type="hidden" name="storage_data[monday_rule]" value="N">
        <input type="checkbox" name="storage_data[monday_rule]" id="elm_storage_monday_rule" value="Y" {if $storage.monday_rule != 'N'} checked="checked" {/if} />
    </div>
</div>

<div class="control-group">
    <label for="elm_storage_holidays" class="control-label">{__("calendar_delivery.holidays")}:</label>
    <div class="controls">
        {include file="addons/calendar_delivery/components/multicalendar.tpl" date_id="elm_storage_holidays" date_name="storage_data[holidays]" date_val=$storage.holidays start_year=$settings.Company.company_start_year}
    </div>
</div>
