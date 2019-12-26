{if $runtime.mode == "add"}
	{if $smarty.request.company_id}
	<input type="hidden" name="user_data[company_id]" value="{$smarty.request.company_id}" />
	{/if}
	{if $smarty.request.usergroup_id}
	<input type="hidden" name="user_data[usergroup_ids]" value="{$smarty.request.usergroup_id}" />
	{/if}
{/if}