<div id="content_categories">
    <div class="control-group">
        <label class="control-label" for="elm_categories">{__("categories")}:</label>
        <div class="controls">
            {include file="pickers/categories/picker.tpl"
                multiple=true
                input_name="promotion_data[categories]"
                item_ids=$promotion_data.categories
                data_id="category_ids"
                no_item_text=__("no_categories_available")
                use_keys="N"
                but_meta="pull-right"
            }
        </div>
    </div>
<!--content_categories--></div>

<div class="control-group">
    <label class="control-label" for="elm_view_separate">{__("view_separate")}:</label>
    <div class="controls">
        <input type="hidden" name="promotion_data[view_separate]" value="N">
        <input type="checkbox" name="promotion_data[view_separate]" {if $promotion_data.view_separate == "Y"}checked="checked"{/if} id="elm_promotion_view_separate" value="Y">
    </div>
</div>

{if $runtime.mode == 'update' && ($addons.product_stickers.vendor_stickers == 'Y' || !("MULTIVENDOR"|fn_allowed_for && $runtime.company_id))}
{include file="common/subheader.tpl" title=__("stickers") target="#stickers_promotion_setting"}
<div id="stickers_promotion_setting" class="in collapse">
    <label for="promotion_ids" class="control-label">{__("stickers")}:</label>
    <div class="controls">
        {include file="addons/product_stickers/pickers/stickers/picker.tpl" data_id="stickers" input_name="promotion_data[sticker_ids]" item_ids=','|explode:$promotion_data.sticker_ids hide_link=true hide_delete_button=true display_input_id="sticker_ids" disable_no_item_text=true view_mode="list" but_meta="btn" hide_input=Y}
    </div>
</div>
{/if}
