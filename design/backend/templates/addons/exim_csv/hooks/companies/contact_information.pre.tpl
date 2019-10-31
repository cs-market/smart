<div class="control-group">
    <label class="control-label" for="elm_company_export_order_to_csv">{__("export_order_to_csv")}:</label>
    <div class="controls">
	    <input type="hidden" name="company_data[export_order_to_csv]"" value="N" />
	    <input type="checkbox" name="company_data[export_order_to_csv]" id="elm_company_export_order_to_csv" value="Y" {if $company_data.export_order_to_csv == 'Y'}checked="checked"{/if}" />
    </div>
</div>