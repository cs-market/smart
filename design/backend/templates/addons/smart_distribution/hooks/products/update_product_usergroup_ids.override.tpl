{*<div class="control-group">
    <label class="control-label">{__("usergroups")}</label>
    <div class="controls">
        {include file="addons/smart_distribution/components/usergroup_picker/picker.tpl"
            input_name="product_data[usergroup_ids][]"
            show_advanced=false
            item_ids=','|explode:$product_data.usergroup_ids
            multiple=true
            allow_clear=true
            allow_add=false
            company_id=$product_data.company_id
        }
    </div>
</div>
*}
