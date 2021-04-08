{if $runtime.mode == 'update' && ($addons.product_stickers.vendor_stickers == 'Y' || !("MULTIVENDOR"|fn_allowed_for && $runtime.company_id))}
{include file="common/subheader.tpl" title=__("stickers") target="#stickers_category_setting"}
<div id="stickers_category_setting" class="in collapse">
    <label for="categories_{$rnd}_ids" class="control-label">{__("stickers")}:</label>
    <div class="controls">
        {include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="category_data[sticker_ids]" item_ids=','|explode:$category_data.sticker_ids hide_link=true hide_delete_button=true display_input_id="sticker_ids" disable_no_item_text=true view_mode="list" but_meta="btn" hide_input=Y}
    </div>
</div>
{/if}
