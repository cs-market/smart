{if $field == "group_id"}
    {assign var="elm_name" value="override_products_data[`$field`]"}

    {if $product.product_id}
        {assign var="result_id" value="field_`$field`_`$product.product_id`_"}
    {else}
        {assign var="result_id" value="field_`$field`_0_"}
    {/if}

    <div class="clear" id="field_{$field}__">
        <div class="correct-picker-but">
            <input type="hidden" name="{$elm_name}" id="{$result_id}" value="{$product.$field}" disabled="disabled" />
            {include file="common/ajax_select_object.tpl" data_url="product_groups.get_groups_list&status=A" text=$product.$field|fn_get_product_group_name result_elm=$result_id id="override_prod_`$product.product_id`"}
        </div>
    </div>
{/if}
