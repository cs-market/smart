<div class="control-group" id="product_group_selector">
    <label class="control-label" for="group_id">{__("product_groups.product_group")}</label>
    <div class="controls">
        <input type="hidden" name="product_data[group_id]" id="group_id" value="{$product_data.group_id}" />
        <div class="text-type-value ajax-select-wrap">
            {include file="common/ajax_select_object.tpl" data_url="product_groups.get_groups_list&status=A" text=$product_data.group_id|fn_get_product_group_name result_elm="group_id" id="`$id`_selector"}
        </div>
    </div>
<!--product_group_selector--></div>
