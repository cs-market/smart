{if !$auth.user_id|fn_smart_distribution_is_manager}
	{if $user_data.user_type == 'V'}
		<div class="control-group">
			<label class="control-label" for="is_manager">{__("is_manager")}</label>
			<div class="controls">
				<input type="hidden" name="user_data[is_manager]" value="N" />
				<input id="is_manager" {if $user_data.is_manager == "Y"}checked="checked"{/if}  type="checkbox" name="user_data[is_manager]" value="Y" />
			</div>
		</div>
	{elseif $user_data.user_type == 'C'}
		{include file="common/subheader.tpl" title=__("manager")}
		<div class="control-group">
			<div class="controls cm-no-hide-input">
			<input class="cm-no-hide-input" type="hidden" name="user_id" value="{$id}" >
				{$extra_url = "&user_type=V"}
				{include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url view_mode="mixed" item_ids=$managers|array_keys data_id="user_id" input_name="managers"}
			</div>
		</div>
	{/if}
{/if}