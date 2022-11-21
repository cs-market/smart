<div class="ty-grid-list ty-grid-list__promotions" >
    {foreach $promotions as $promotion_id => $promotion}
            <div class="ty-grid-list__item  ty-flex-column ty-grid-list__item--overlay">
            	<a href="{"promotions.view&promotion_id=$promotion_id"|fn_url}">
                {if $promotion.image}
                    {include file="common/image.tpl"
                        images=$promotion.image
                        image_id="promotion_image"
                        class="ty-grid-promotions__image"
                    }
                {/if}
                    {if $promotion.to_date}
                        <div class="ty-grid-list__available">
                            {__("avail_till")}: {$promotion.to_date|date_format:$settings.Appearance.date_format}
                        </div>
                    {/if}
                    {if "MULTIVENDOR"|fn_allowed_for && ($company_name || $promotion.company_id)}
                        <div class="ty-grid-promotions__company">
                            <a href="{"companies.products?company_id=`$company_id`"|fn_url}" class="ty-grid-promotions__company-link">
                                {if $company_name}{$company_name}{else}{$promotion.company_id|fn_get_company_name}{/if}
                            </a>
                        </div>
                    {/if}
                    <h2 class="ty-grid-promotions__header">{$promotion.name}</h2>
                    {if $promotion.detailed_description}
                        <div class="ty-wysiwyg-content ty-grid-promotions__description">
                            {$promotion.detailed_description nofilter}
                        </div>
                    {/if}
            	</a>
            </div>
    {/foreach}
</div>
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
</div>

{capture name="mainbox_title"}{__("active_promotions")}{/capture}
