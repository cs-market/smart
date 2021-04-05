<div class="control-group">
    <label class="control-label" for="elm_company_notify_manager_order_insufficient">{__("notify_manager_order_insufficient")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[notify_manager_order_insufficient]" value="N" />
        <input type="checkbox" name="company_data[notify_manager_order_insufficient]" id="elm_company_notify_manager_order_insufficient" value="Y" {if $company_data.notify_manager_order_insufficient == 'Y'}checked="checked"{/if} />
    </div>
</div>
