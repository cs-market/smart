<input type="hidden" name="shipping_data[service_params][configured]" value="Y" />

<div class="control-group">
	<label class="control-label" for="elm_limit_weekday">{__("calendar_delivery.limit_weekday")}</label>
	<div class="controls">
		<select id="elm_limit_weekday" name="shipping_data[service_params][limit_weekday]">
			<option value="" {if $shipping.service_params.limit_weekday === false}selected="selected"{/if}>{__("any")}</option>
			<option value="1" {if $shipping.service_params.limit_weekday == "1"}selected="selected"{/if}>{__("weekday_1")}</option>
			<option value="2" {if $shipping.service_params.limit_weekday == "2"}selected="selected"{/if}>{__("weekday_2")}</option>
			<option value="3" {if $shipping.service_params.limit_weekday == "3"}selected="selected"{/if}>{__("weekday_3")}</option>
			<option value="4" {if $shipping.service_params.limit_weekday == "4"}selected="selected"{/if}>{__("weekday_4")}</option>
			<option value="5" {if $shipping.service_params.limit_weekday == "5"}selected="selected"{/if}>{__("weekday_5")}</option>
			<option value="6" {if $shipping.service_params.limit_weekday == "6"}selected="selected"{/if}>{__("weekday_6")}</option>
			<option value="0" {if $shipping.service_params.limit_weekday == "0"}selected="selected"{/if}>{__("weekday_0")}</option>
			<option value="C" {if $shipping.service_params.limit_weekday == "C"}selected="selected"{/if}>{__("calendar_delivery.from_customer_settings")}</option>
		</select>
	</div>
</div>