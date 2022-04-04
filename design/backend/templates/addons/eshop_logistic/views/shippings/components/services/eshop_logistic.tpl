
<fieldset>

<div class="control-group">
	<label class="control-label" for="ship_eshop_logistic_height">{__("eshop_logistic.use_auto_image")}:</label>
	<div class="controls">
		<input type="hidden" name="shipping_data[service_params][use_auto_image]" value={"YesNo::NO"|enum}/>
		<input id="ship_eshop_logistic_use_auto_image" type="checkbox" name="shipping_data[service_params][use_auto_image]" value={"YesNo::YES"|enum} {if $shipping.service_params.use_auto_image == "YesNo::YES"|enum}checked{/if} />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_eshop_logistic_height">{__("ship_height")}:</label>
	<div class="controls">
		<input id="ship_eshop_logistic_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_eshop_logistic_width">{__("ship_width")}:</label>
	<div class="controls">
		<input id="ship_eshop_logistic_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="ship_eshop_logistic_length">{__("ship_length")}:</label>
	<div class="controls">
		<input id="ship_eshop_logistic_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
	</div>
</div>

</fieldset>

