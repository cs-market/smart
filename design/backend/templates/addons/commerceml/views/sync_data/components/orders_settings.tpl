<fieldset>
    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_shipping">{__("commerceml.cml_order_export_shipping")}:<div class="muted description">{__("commerceml.cml_order_export_shipping.tooltip")}</div></label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][order][export_shipping]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_export_shipping" type="checkbox" name="sync_data_settings[{$sync_provider_id}][order][export_shipping]" value="Y" {if $sync_settings.order.export_shipping == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_product_options">{__("commerceml.cml_order_export_product_options")}:<div class="muted description">{__("commerceml.cml_order_export_product_options.tooltip")}</div></label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][order][export_product_options]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_export_product_options" type="checkbox" name="sync_data_settings[{$sync_provider_id}][order][export_product_options]" value="Y" {if $sync_settings.order.export_product_options == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_from_order_id">{__("commerceml.cml_order_export_from_order_id")}:<div class="muted description">{__("commerceml.cml_order_export_from_order_id.tooltip")}</div></label>
        <div class="controls">
            <input id="elm_sync_data_commerceml_cml_order_export_from_order_id" type="text" name="sync_data_settings[{$sync_provider_id}][order][export_from_order_id]" size="30" value="{$sync_settings.order.export_from_order_id}" class="user-success" aria-invalid="false">
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_import_statuses">{__("commerceml.cml_order_import_statuses")}:<div class="muted description">{__("commerceml.cml_order_import_statuses.tooltip")}</div></label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][order][import_statuses]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_import_statuses" type="checkbox" name="sync_data_settings[{$sync_provider_id}][order][import_statuses]" value="Y" {if empty($sync_settings.order.import_statuses) || $sync_settings.order.import_statuses == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_statuses">{__("commerceml.cml_order_export_statuses")}:<div class="muted description">{__("commerceml.cml_order_export_statuses.tooltip")}</div></label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][order][export_statuses]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_export_statuses" type="checkbox" name="sync_data_settings[{$sync_provider_id}][order][export_statuses]" value="Y" {if empty($sync_settings.order.export_statuses) || $sync_settings.order.export_statuses == "Y"}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_statuses_filter">{__("commerceml.cml_order_statuses_filter")}:<div class="muted description">{__("commerceml.cml_order_statuses_filter.tooltip")}</div></label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][order][statuses_filter]" value="N">
            {foreach $order_statuses as $status_code => $status}
                <label for="variant_cml_order_statuses_filter_{$status_code}" class="checkbox">
                    <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][order][statuses_filter][]" id="variant_cml_order_statuses_filter_{$status_code}" value="{$status_code}" class="user-success">{$status}
                </label>
            {/foreach}
        </div>
    </div>
</fieldset>