<div class="control-group">
    <label class="control-label" for="elm_company_notify_manager_order_create">{__("notify_manager_order_create")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[notify_manager_order_create]"" value="N" />
        <input type="checkbox" name="company_data[notify_manager_order_create]" id="elm_company_notify_manager_order_create" value="Y" {if $company_data.notify_manager_order_create == 'Y'}checked="checked"{/if}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="elm_company_notify_manager_order_update">{__("notify_manager_order_update")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[notify_manager_order_update]"" value="N" />
        <input type="checkbox" name="company_data[notify_manager_order_update]" id="elm_company_notify_manager_order_update" value="Y" {if $company_data.notify_manager_order_update == 'Y'}checked="checked"{/if}" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="elm_company_tracking">{__("product_tracking")}:</label>
    <div class="controls">
        <select name="company_data[tracking]">
            <option value="{"ProductTracking::TRACK"|enum}" {if $company_data.tracking == "ProductTracking::TRACK"|enum} selected="selected" {/if}>{__("yes")}</option>
            <option value="{"ProductTracking::DO_NOT_TRACK"|enum}" {if $company_data.tracking == "ProductTracking::DO_NOT_TRACK"|enum} selected="selected" {/if}>{__("no")}</option>
        </select>
    </div>
</div>
