{$addon = 'product_stickers'}
{if !$addons.$addon.license_key}
	<script type="text/javascript">
	Tygh.$(document).ready(function(){
		{if $runtime.controller == 'addons' && $runtime.mode == 'manage'}
			Tygh.$('#opener_group{$addon}installed').click();
		{else}
			Tygh.$.redirect('{'addons.manage'|fn_url}');
		{/if}
	});
	</script>
{/if}