{$addon = 'decimal_amount'}
{if !$addons.$addon.license_key}
	<script type="text/javascript">
	Tygh.$(document).ready(function(){
		{if $runtime.controller == 'addons' && $runtime.mode == 'manage'}
			Tygh.$('[id ^= opener_group{$addon}').click();
		{else}
			Tygh.$.redirect('{'addons.manage'|fn_url}');
		{/if}
	});
	</script>
{/if}