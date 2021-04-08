{if $field == "sticker_ids"}
    {include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="override_products_data[sticker_ids]" item_ids=$product.sticker_ids hide_link=true hide_delete_button=true display_input_id="sticker_ids" disable_no_item_text=true view_mode="list" but_meta="btn" hide_input='Y'}
{/if}
