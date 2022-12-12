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
