<div class="control-group">
	<label class="control-label" for="elm_company_export_order_to_xml">{__("export_order_to_xml")}:</label>
	<div class="controls">
		<input type="hidden" name="company_data[export_order_to_xml]" value="N" />
		<input type="checkbox" name="company_data[export_order_to_xml]" id="elm_company_export_order_to_xml" value="Y" {if $company_data.export_order_to_xml == 'Y'} checked="checked"{/if} />
	</div>
</div>