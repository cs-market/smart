{if $usergroup.type == 'C'}
<div class="control-group">
    <label class="control-label" for="elm_working_time_till_{$id}">{__('calendar_delivery.working_time_till')}</label>
    <div class="controls">
        <input class="input-time cm-trim user-success" id="elm_company_working_time_till" size="5" maxlength="5" type="text" name="usergroup_data[working_time_till]" value="{$usergroup.working_time_till}" placeholder="00:00">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_delivery_date">{__("delivery_date")}</label>
    <div class="controls">
        {include file="addons/calendar_delivery/components/weekdays_table.tpl" name="usergroup_data[delivery_date]" value=$usergroup.delivery_date|default:"1111111"}
    </div>
</div>
{/if}
