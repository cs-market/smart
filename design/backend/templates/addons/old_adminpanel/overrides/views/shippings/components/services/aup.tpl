<fieldset>

    <div class="control-group">
	<label class="control-label" for="elm_aup_pac_api_key">{__("shippings.aup.pac_api_key")}</label>
	<div class="controls">
            <input id="elm_aup_pac_api_key" type="text" name="shipping_data[service_params][pac_api_key]" value="{$shipping.service_params.pac_api_key}" />
	</div>
    </div>

    <div class="control-group">
	<label class="control-label cm-required" for="elm_aup_ship_width">{__("ship_width")}</label>
	<div class="controls">
            <input class="cm-value-decimal" id="elm_aup_ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width|default:0}" />
	</div>
    </div>

    <div class="control-group">
	<label class="control-label cm-required" for="elm_aup_ship_height">{__("ship_height")}</label>
	<div class="controls">
            <input class="cm-value-decimal" id="elm_aup_ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height|default:0}" />
	</div>
    </div>

    <div class="control-group">
	<label class="control-label cm-required" for="elm_aup_ship_length">{__("ship_length")}</label>
	<div class="controls">
            <input class="cm-value-decimal" id="elm_aup_ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length|default:0}"/>
	</div>
    </div>

</fieldset>
