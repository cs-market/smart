{if $usergroup.type == 'C'}
{include file="common/subheader.tpl" title=__("set_users")}
<div class="control-group">
    <div class="controls cm-no-hide-input">
	<input class="cm-no-hide-input" type="hidden" name="user_id" value="{$id}" >
	{$extra_url = "&user_type=C"}
	{include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url view_mode="mixed" data_id="user_id" input_name="assing_users"}
    </div>
</div>
{/if}