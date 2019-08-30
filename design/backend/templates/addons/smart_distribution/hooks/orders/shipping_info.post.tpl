{if $order_info.product_groups.0.package_info_full.packages.0.weight}
	<div class="control-group">
	    <div class="control-label">{__('weight')} ({$settings.General.weight_symbol nofilter})</div>
	    <div id="tygh_weight" class="controls">{$order_info.product_groups.0.package_info_full.packages.0.weight}</div>
	</div>
{/if}