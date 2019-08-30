{if $user_data.user_type == 'C'}
	{include file="common/subheader.tpl" title=__("sales_plan")}
	<div class="table-wrapper">
		<table class="table table-middle" width="100%">
			<thead class="cm-first-sibling">
				<tr>
					<th width="20%">{__('vendor')}</th>
					<th width="20%">{__('sales_plan')}</th>
					<th width="20%">{__('frequency')}</th>
					<th width="10%"></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$plan item="item" }
				<tr class="cm-row-item" id="box_company_plan_{$item.company_id}">
					<td>
						{$item.company_id|fn_get_company_name}
					</td>
					<td>
						<input type="text" name="plan[{$item.company_id}][amount_plan]" value="{$item.amount_plan}" size="10" class="input-medium cm-no-hide-input">
					</td>
					<td>
						<input type="text" name="plan[{$item.company_id}][frequency]" value="{$item.frequency}" size="10" class="input-medium cm-no-hide-input">
					</td>
					<td class="right nowrap">
						<div class="hidden-tools">
						{include file="buttons/multiple_buttons.tpl" item_id="company_plan_`$item.company_id`"  only_delete="Y"}
						</div>
					</td>
				</tr>
				{/foreach}
				{if !($runtime.company_id && $plan)}
				<tr class="cm-row-item">
					<td>
						{include file="views/companies/components/company_field.tpl"
							name="plan[add][company_id]"
							id="plan_company_id"
							no_wrap=true
						}
						{if $runtime.company_id}<input type="hidden" name="plan[add][company_id]" value="{$runtime.company_id}" size="10" class="cm-no-hide-input">{/if}
					</td>
					<td>
						<input type="text" name="plan[add][amount_plan]" value="" size="10" class="input-medium cm-no-hide-input">
					</td>
					<td>
						<input type="text" name="plan[add][frequency]" value="" size="10" class="input-medium cm-no-hide-input">
					</td>
					<td></td>
				</tr>
				{/if}
			</tbody>
		</table>
	</div>
{/if}