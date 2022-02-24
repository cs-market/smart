{if $usergroup.type == 'C'}
<div class="control-group">
    <label class="control-label" for="elm_working_time_till_{$id}">{__('calendar_delivery.working_time_till')}</label>
    <div class="controls">
        <input class="input-time cm-trim user-success" id="elm_company_working_time_till" size="5" maxlength="5" type="text" name="usergroup_data[working_time_till]" value="{$usergroup.working_time_till}" placeholder="00:00">
    </div>
</div>
{/if}
