{include file="common/letter_header.tpl"}

{if $data.not_placed}
	<p>{__('sales_plan.not_placed')}</p>
	<ul>
		{foreach from=$data.not_placed item='item'}
			<li>{$item|fn_get_user_name}</li>
		{/foreach}
	</ul>
{/if}

{if $data.less_placed}
	<p>{__('sales_plan.less_placed')}</p>
	<ul>
		{foreach from=$data.less_placed item='item'}
			<li>{$item|fn_get_user_name}</li>
		{/foreach}
	</ul>
{/if}

{include file="common/letter_footer.tpl"}