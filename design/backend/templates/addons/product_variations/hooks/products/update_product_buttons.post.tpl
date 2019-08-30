{if $product_data.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_CONFIGURABLE"|constant}
    {include file="buttons/button.tpl" but_meta="cm-tab-tools hidden" but_id="tools_variations_btn" but_text=__("save") but_name="dispatch[products.m_update]" but_role="submit-link" but_target_form="manage_product_variations_form"}
{/if}
