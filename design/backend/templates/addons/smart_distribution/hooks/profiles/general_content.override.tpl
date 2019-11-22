{hook name="profiles:general_content"}
	{include file="views/profiles/components/profiles_account.tpl"}

	{if (($user_type == "V") && $id != $auth.user_id) || $user_type == "C"}

		{$zero_company_id_name_lang_var = false}
		{if "ULTIMATE"|fn_allowed_for && $user_type|fn_check_user_type_admin_area}
			{$zero_company_id_name_lang_var = 'all_vendors'}
		{/if}

        {if "MULTIVENDOR"|fn_allowed_for}
            {assign var="zero_company_id_name_lang_var" value="none"}
        {/if}

		{include file="views/companies/components/company_field.tpl"
			name="user_data[company_id]"
			id="user_data_company_id"
			selected=$user_data.company_id
			zero_company_id_name_lang_var=$zero_company_id_name_lang_var
			disable_company_picker=$hide_inputs
		}

	{else}
		<input type="hidden" name="user_data[company_id]" value="{$user_data.company_id|default:0}">
	{/if}
{/hook}