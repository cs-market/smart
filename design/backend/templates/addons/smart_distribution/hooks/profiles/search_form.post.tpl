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
<div class="control-group">
	<label class="control-label" for="elm_managers">{__("user_type")}</label>
	<div class="controls">
		<select id="user_type" name="user_type">
			<option value="">{__("all")}</option>
			<option value="C" {if $search.user_type == "C"}selected="selected"{/if}>{__("customer")}</option>
			<option value="V" {if $search.user_type == "V"}selected="selected"{/if}>{__("vendor_administrator")}</option>
			<option value="A" {if $search.user_type == "A"}selected="selected"{/if}>{__("administrator")}</option>
		</select>
	</div>
</div>

{include
    file="common/period_selector.tpl"
    period=$search.without_order_period
    prefix="without_order_"
    display="form"
}
