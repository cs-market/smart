{if "MULTIVENDOR"|fn_allowed_for}
    <div class="control-group">
        <label class="control-label" for="elm_company_notify_manager_order_create">{__("managers.notify_manager_order_create")}:</label>
        <div class="controls">
            <input type="hidden" name="company_data[notify_manager_order_create]" value="N" />
            <input type="checkbox" name="company_data[notify_manager_order_create]" id="elm_company_notify_manager_order_create" value="Y" {if $company_data.notify_manager_order_create == 'Y'}checked="checked"{/if}  />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_company_notify_manager_order_update">{__("managers.notify_manager_order_update")}:</label>
        <div class="controls">
            <input type="hidden" name="company_data[notify_manager_order_update]" value="N" />
            <input type="checkbox" name="company_data[notify_manager_order_update]" id="elm_company_notify_manager_order_update" value="Y" {if $company_data.notify_manager_order_update == 'Y'}checked="checked"{/if} />
        </div>
    </div>
{/if}
