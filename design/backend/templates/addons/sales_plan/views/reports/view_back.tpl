{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

{assign var="r_url" value=$config.current_url|escape:url}

<div class="items-container" >


	<input type="hidden" name="result_ids" value="manage_robots" />
	{if $report}
		<div class="table-responsive-wrapper" style="max-width: 960px; overflow-x: scroll;">
			
		
		<table class="table table-middle table-responsive" width="100%">
			<thead>
				<tr>
					{foreach from=$report[0]|array_keys item="header"}
						<th>{$header}</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach from=$report item="item"}
				<tr>
					{foreach from=$item item="value"}
					<td>
						{$value}
					</td>
					{/foreach}
				</tr>
				{/foreach}
			</tbody>

		</table>
		</div>
	{else}
		<p class="no-items">{__("no_data")}</p>
	{/if}

<!--manage_robots--></div>

{/capture}

{capture name="sidebar"}
	<div class="sidebar-row">
	<h6>{__("filter")}</h6>
	<form name="thread_search_form" action="{""|fn_url}" method="get" class="{$form_meta}" id="sales_plans_form">
		<input type="hidden" name="is_search" value="Y">

		<div class="sidebar-field">
			<label for="elm_customer">{__("customer")}</label>
			<div class="break">
		   		{include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url user_info=$search.user_ids data_id="0" input_name="user_ids"}
			</div>
		</div>
		<div class="sidebar-field">
			<label for="elm_manager">{__("manager")}</label>
			<div class="break">
				<select name="managers" id="elm_manager">
					<option value="">--</option>
					{$managers = ""|fn_smart_distribution_get_managers}
					{foreach from=$managers item="manager" key="user_id"}
						<option value="{$user_id}" {if $search.managers == $user_id} selected="selected" {/if}>{$manager.name}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="sidebar-field">
			<label for="elm_manager">{__("usergroup")}</label>
			<div class="break">
				<select name="usergroup_id" id="elm_manager">
					<option value="">--</option>
					{assign var="usergroups" value="C"|fn_get_usergroups}
					{foreach from=$usergroups item="usergroup" key="usergroup_id"}
						<option value="{$usergroup_id}" {if $search.usergroup_id == $usergroup_id} selected="selected" {/if}>{$usergroup.usergroup}</option>
					{/foreach}
				</select>
			</div>
		</div>
		{if !$runtime.company_id}
			{include file="views/companies/components/company_field.tpl"
				name="company_id"
				id="elm_company_id"
				zero_company_id_name_lang_var="none"
				selected=$search.company_id
				disable_company_picker=$disable_company_picker
			}
			
		{/if}
		{include file="common/period_selector.tpl" period=$search.period display="form"}
		{*<div class="sidebar-field">
			<label for="elm_export_file">{__("export_file")}</label>
			<div class="break">
				<select name="output" id="output">
				<option value="D" {if $value == "D"}selected="selected"{/if}>{__("direct_download")}</option>
				<option value="C" {if $value == "C"}selected="selected"{/if}>{__("screen")}</option>
				{if !$runtime.company_id || !empty($runtime.simple_ultimate)}
					<option value="S" {if $value == "S"}selected="selected"{/if}>{__("server")}</option>
				{/if}
				</select>
				<input type="hidden" name="output" value="D" />
				<input type="checkbox" name="output" id="elm_export_file" value="F" {if $search.output == 'F'}selected="selected"{/if} />
			</div>
		</div>*}
		<div class="sidebar-field clearfix">
			<div class="break pull-left">
				<input type="hidden" name="summ" value="N">
				<input id="elm_summ" type="checkbox" checked="checked" name="summ" value="Y">
			</div>
			<label for="elm_summ" class="pull-left">{__("sales_plan.summ")}</label>
		</div>
		<div class="sidebar-field clearfix">
			<div class="break pull-left">
				<input type="hidden" name="amount" value="N">
				<input id="elm_amount" type="checkbox" checked="checked" name="amount" value="Y">
			</div>
			<label for="elm_amount" class="pull-left">{__("sales_plan.amount")}</label>
		</div>
		<div class="sidebar-field clearfix">
			<div class="break pull-left">
				<input type="hidden" name="average" value="N">
				<input id="elm_average" type="checkbox" checked="checked" name="average" value="Y">
			</div>
			<label for="elm_average" class="pull-left">{__("sales_plan.average")}</label>
		</div>
		<div class="sidebar-field">
			<label for="elm_only_zero" class="pull-left">{__("sales_plan.only_zero")}</label>
			<div class="break">
				<input type="hidden" name="only_zero" value="N">
				<input id="elm_only_zero" type="checkbox" name="only_zero" {if $search.only_zero == 'Y'} checked="checked" {/if} value="Y">
			</div>
		</div>
		<div class="sidebar-field">
			<label for="elm_group_by">{__("sales_plan.group_by")}</label>
			<div class="break">
				<select name="group_by" id="elm_group_by">
					<option value="day" {if $search.group_by == 'day'}selected="selected"{/if}>{__('day')}</option>
					<option value="week" {if $search.group_by == 'week'}selected="selected"{/if}>{__('week')}</option>
					<option value="month" {if $search.group_by == 'month'}selected="selected"{/if}>{__('month')}</option>
				</select>
			</div>
		</div>
		{include file="buttons/button.tpl"  but_name="dispatch[sales_plan.view]" but_role="submit-button" but_target_form="sales_plans_form" but_text=__('search')}
		{include file="buttons/button.tpl" but_name="dispatch[sales_plan.view.csv]" but_role="submit-button" but_target_form="sales_plans_form" but_text=__('export') but_meta="cm-new-window pull-right"}
	</form></div>
	<script type="text/javascript">
		$('#elm_only_zero').click(function () {
			if ($(this).prop( "checked" )) {
				select = $("select[id$='period_selects']", $(this).closest('form'));
				$("option[value='D']", select).prop('selected', true).change();
			}
		});
	</script>
{/capture}


{include file="common/mainbox.tpl" title=__("sales_report") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
