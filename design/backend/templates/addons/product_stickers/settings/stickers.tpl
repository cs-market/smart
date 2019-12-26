{* display_input_id  !!! *}

<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.on_sale_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[sale_sticker_id]" item_ids=$settings_data.general.sale_sticker_id hide_link=true hide_delete_button=true display_input_id="sale_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
	</div>
</div>
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.novelty_sticker")}:</label>
	<div class="controls" >
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[novelty_sticker_id]" item_ids=','|explode:$settings_data.general.novelty_sticker_id hide_link=true hide_delete_button=true display_input_id="novelty_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
		{__("stickers.during")}&nbsp;<input type="text" name="settings_data[novelty_days]" id="novelty_time" size="60" value="{$settings_data.general.novelty_days}" class="input-micro" >&nbsp;{__("stickers.days")}
	</div>
</div>
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.coming_soon_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[coming_soon_sticker_id]" item_ids=','|explode:$settings_data.general.coming_soon_sticker_id hide_link=true hide_delete_button=true display_input_id="coming_soon_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
		{__("stickers.during")}&nbsp;<input type="text" name="settings_data[coming_soon_days]" id="novelty_time" size="60" value="{$settings_data.general.coming_soon_days}" class="input-micro" >&nbsp;{__("stickers.days_before_selling")}
	</div>
</div>
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.free_shipping_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[free_shipping_sticker_id]" item_ids=','|explode:$settings_data.general.free_shipping_sticker_id hide_link=true hide_delete_button=true display_input_id="free_shipping_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
	</div>
</div>
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.most_popular_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[most_popular_sticker_id]" item_ids=','|explode:$settings_data.general.most_popular_sticker_id hide_link=true hide_delete_button=true display_input_id="most_popular_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
		{__("stickers.popularity_more")}&nbsp;<input type="text" name="settings_data[popularity]" id="novelty_time" size="60" value="{$settings_data.general.popularity}" class="input-micro" >
	</div>
</div>
{if $addons.bestsellers.status == 'A'}
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.bestseller_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[bestseller_sticker_id]" item_ids=','|explode:$settings_data.general.bestseller_sticker_id hide_link=true hide_delete_button=true display_input_id="bestseller_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
		{__("stickers.sales_count")}&nbsp;<input type="text" name="settings_data[sales_count]" id="sales_count" size="60" value="{$settings_data.general.sales_count}" class="input-micro" >
	</div>
</div>
{/if}
{if $addons.discussion.status == 'A'}
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.top_rated_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[top_rated_sticker_id]" item_ids=','|explode:$settings_data.general.top_rated_sticker_id hide_link=true hide_delete_button=true display_input_id="top_rated_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
		{__("stickers.rating_equal")}&nbsp;<input type="text" name="settings_data[rating_equal]" id="rating_equal" size="60" value="{$settings_data.general.rating_equal}" class="input-micro" >
	</div>
</div>
{/if}
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.sold_out_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[sold_out_sticker_id]" item_ids=','|explode:$settings_data.general.sold_out_sticker_id hide_link=true hide_delete_button=true display_input_id="sold_out_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
	</div>
</div>
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.in_stock_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[in_stock_sticker_id]" item_ids=','|explode:$settings_data.general.in_stock_sticker_id hide_link=true hide_delete_button=true display_input_id="in_stock_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
	</div>
</div>
{if $addons.rma.status == 'A'}
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.returnable_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[returnable_sticker_id]" item_ids=','|explode:$settings_data.general.returnable_sticker_id hide_link=true hide_delete_button=true display_input_id="returnable_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
	</div>
</div>
{/if}
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.weight_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[weight_sticker_id]" item_ids=','|explode:$settings_data.general.weight_sticker_id hide_link=true hide_delete_button=true display_input_id="weight_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
		<select name="settings_data[weight_condition]" class='input-medium'>
			<option {if $settings_data.general.weight_condition == 'greater'}selected="selected"{/if} value="greater">{__('promotion_op_gt')}</option>
			<option {if $settings_data.general.weight_condition == 'less'}selected="selected"{/if} value="less">{__('less')}</option>
		</select>
		&nbsp;{__('stickers.than')}
		<input type="text" name="settings_data[weight_value]" size="60" value="{$settings_data.general.weight_value}" class="input-micro" >
		{$settings.General.weight_symbol}
	</div>
</div>
{if $addons.age_verification.status == 'A'}
<div class="control-group setting-wide">
	<label for="products_{$rnd}_ids" class="control-label">{__("stickers.age_verification_sticker")}:</label>
	<div class="controls">
		<div class='sticker-picker'>
			{include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="settings_data[age_verification_sticker_id]" item_ids=','|explode:$settings_data.general.age_verification_sticker_id hide_link=true hide_delete_button=true display_input_id="age_verification_sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
		</div>
	</div>
</div>
{/if}
