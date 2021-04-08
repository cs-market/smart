<div class="control-group">
    <label class="control-label" for="elm_feature_suffix_{$id}">{__("stickers")}</label>
    <div class="controls">
    {include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="feature_data[sticker_ids]" item_ids=$feature.sticker_ids hide_link=true hide_delete_button=true display_input_id="sticker_id" disable_no_item_text=true view_mode="radio" but_meta="btn" hide_input=Y default_name="None"}
    </div>
</div>
