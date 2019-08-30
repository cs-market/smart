{$managers = ""|fn_smart_distribution_get_managers}
<div class="control-group">
	<label class="control-label" for="elm_managers">{__("manager")}</label>
	<div class="controls">
	<select name="managers" id="elm_managers">
	<option value="">--</option>
		{foreach from=$managers item="manager" key="user_id"}
			<option value="{$user_id}">{$manager.name}</option>
		{/foreach}
	</select>
	</div>
</div>