{hook name="promotion_data:view"}
<div class="ty-promotion-page">
    <div class="ty-image-container">{if $promotion_data.image}{include file="common/image.tpl" images=$promotion_data.image}{/if}</div>
    <div class="ty-promotion-page__body">
        {hook name="promotion_data:view_description"}
        {if $promotion_data.detailed_description || $runtime.customization_mode.live_editor}
            {$promotion_data.detailed_description nofilter}
        {/if}
        {/hook}
    </div>
</div>

<div id="category_products_{$block.block_id}">
    {if $products}
        {assign var="layouts" value=""|fn_get_products_views:false:0}
        {$is_selected_filters = $smarty.request.features_hash}
        {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
        {if $layouts.$selected_layout.template}
            {include file="`$layouts.$selected_layout.template`" columns=$product_columns+1}
        {/if}

    {elseif !$show_not_found_notification && $is_selected_filters}
        {include file="common/no_items.tpl"
            text_no_found=__("text_no_products_found")
            no_items_extended=true
            reset_url=$config.current_url|fn_query_remove:"features_hash"
        }
    {elseif !$subcategories || $show_no_products_block}
        {include file="common/no_items.tpl"
            text_no_found=__("text_no_products")
        }
    {else}
    <div class="cm-pagination-container"></div>
    {/if}
<!--category_products_{$block.block_id}--></div>

{capture name="mainbox_title"}<span {live_edit name="promotion:promotion:{$promotion_data.promotion_id}"}>{$promotion_data.promotion}</span>{/capture}
{/hook}
