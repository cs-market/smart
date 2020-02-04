{if $runtime.mode == 'update'}
{include file="common/subheader.tpl" title=__("stickers") target="#stickers_product_setting"}
<div id="stickers_product_setting" class="in collapse">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers")}:</label>
	<div class="controls">
		{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="product_data[sticker_ids]" item_ids=','|explode:$product_data.sticker_ids hide_link=true hide_delete_button=true display_input_id="sticker_ids" disable_no_item_text=true view_mode="list" but_meta="btn" hide_input=Y}
	</div>
</div>
{/if}