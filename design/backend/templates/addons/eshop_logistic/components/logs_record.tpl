{$_log_lvl = $log_lvl + 1}
{foreach from=$data key=_data_key item=_data}
    {if !$_data|is_object && !$_data|is_array}
		{if !empty($_data)}
			<b class="eshop_log_data_{$log_lvl}-lvl">{$_data_key}</b>  =>  {$_data}<br />
		{/if}
    {else}
	<b class="eshop_log_data_{$log_lvl}-lvl">{$_data_key}</b> <br />
		{include file="addons/eshop_logistic/components/logs_record.tpl" data=$_data log_lvl=$_log_lvl}
    {/if}
{/foreach}