<div id="content_categories">
    {include file="pickers/categories/picker.tpl"
        multiple=true
        input_name="promotion_data[categories]"
        item_ids=$promotion_data.categories
        data_id="category_ids"
        no_item_text=__("text_all_categories_included")
        use_keys="N"
        but_meta="pull-right"
    }
<!--content_categories--></div>
