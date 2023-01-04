<div class="hidden" id="content_cross_sell">
{$cross_types = ""|fn_get_crosssell_types}
{foreach from=$cross_types item="related_products" key="related_type"}
    {$def = $cross_types[$related_type]}
    {include file="common/subheader.tpl" title=__($def)}
    {include file="pickers/products/picker.tpl" positions="" input_name="product_data[`$def`]" data_id="added_`$def`" item_ids=$cross_sells[$related_type] type="links"}
{/foreach}
</div>
