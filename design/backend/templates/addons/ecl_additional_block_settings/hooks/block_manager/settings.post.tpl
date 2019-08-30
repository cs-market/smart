{if !"ULTIMATE:FREE"|fn_allowed_for}
	<div class="control-group">
		<label class="control-label" for="block_{$html_id}_usergroups">{__("usergroups")}:</label>
		<div class="controls">
			{include file="common/select_usergroups.tpl" id="block_`$html_id`_usergroups" name="block_data[usergroup_ids]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$block.usergroup_ids input_extra="" list_mode=false}
		</div>
	</div>
	{assign var="ef_ids" value=","|explode:$block.enable_for}
	<div class="control-group">
		<label class="control-label" for="block_{$html_id}_enable_for">{__("enable_for")}:</label>
		<div class="controls">
			<input type="hidden" name="block_data[enable_for]" value="D,T,M">
			<label class="checkbox inline" for="block_{$html_id}_enable_for_1">
				<input type="checkbox" name="block_data[enable_for][]" id="block_{$html_id}_enable_for_1" value="D" {if 'D'|in_array:$ef_ids}checked="checked"{/if} />
				{__('desktop')}
			</label>
			<label class="checkbox inline" for="block_{$html_id}_enable_for_2">
				<input type="checkbox" name="block_data[enable_for][]" id="block_{$html_id}_enable_for_2" value="T" {if 'T'|in_array:$ef_ids}checked="checked"{/if} />
				{__('tablet')}
			</label>
			<label class="checkbox inline" for="block_{$html_id}_enable_for_3">
				<input type="checkbox" name="block_data[enable_for][]" id="block_{$html_id}_enable_for_3" value="M" {if 'M'|in_array:$ef_ids}checked="checked"{/if} />
				{__('mobile')}
			</label>
		</div>
	</div>
{/if}