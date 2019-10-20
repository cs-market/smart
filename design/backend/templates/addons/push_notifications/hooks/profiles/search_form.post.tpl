<div class="control-group">
	<label class="control-label" for="elm_mobile_app">{__("mobile_app")}</label>
	<div class="controls">
		<input type="hidden" name="mobile_app" value="N">
		<input type="checkbox" id="elm_mobile_app" name="mobile_app" value="Y" {if $search.mobile_app == 'Y'}checked="checked"{/if} onclick="Tygh.$.disable_elms(['elm_app_name', 'elm_app_version'], !this.checked);">
	</div>
</div>
{$app_names = 'app_name'|fn_push_notifications_get_mobile_table_values}
{if $app_names}
<div class="control-group">
	<label class="control-label" for="elm_app_name">{__("app_name")}</label>
	<div class="controls">
		<select name="app_name" id="elm_app_name" {if $search.mobile_app != 'Y'}disabled="disabled"{/if}>
			<option value="">--</option>
			{foreach from=$app_names item='app_name'}
				<option value="{$app_name}" {if $search.app_name == $app_name} selected="selected" {/if}>{$app_name}</option>
			{/foreach}
		</select>
	</div>
</div>
{/if}
{$app_versions = 'app_version'|fn_push_notifications_get_mobile_table_values}
{if $app_versions}
<div class="control-group">
	<label class="control-label" for="elm_app_version">{__("version")}</label>
	<div class="controls">
		<select name="app_version" id="elm_app_version" {if $search.mobile_app != 'Y'}disabled="disabled"{/if}>
			<option value="">--</option>
			{foreach from=$app_versions item='version'}
				<option value="{$version}" {if $search.app_version == $version} selected="selected" {/if}>{$version}</option>
			{/foreach}
		</select>
	</div>
</div>
{/if}