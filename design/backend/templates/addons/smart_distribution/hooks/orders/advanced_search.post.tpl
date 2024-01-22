<div class="group">
    <div class="sidebar-field">
        <label class="control-label">{__("ordered_category_products")}</label>
        <div class="controls">
            {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="category_ids" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
        </div>
    </div>
</div>
