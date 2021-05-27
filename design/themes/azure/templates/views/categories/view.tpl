{hook name="categories:view"}
<div id="category_products_{$block.block_id}">
{if $category_data.main_pair}
    <div class="categories_img">
    {*include file="common/image.tpl"
        show_detailed_link=false
        images=$category_data.main_pair
        no_ids=true
        image_id="category_image"
        image_width=200
        image_height=200
    *}
    </div>
{/if}

{if $subcategories}
    {include file="views/categories/components/categories_multicolumns.tpl" categories=$subcategories columns=3}
{/if}

{if $category_data.description || $runtime.customization_mode.live_editor}
    <div class="ty-wysiwyg-content ty-mb-s" {live_edit name="category:description:{$category_data.category_id}"}>{$category_data.description nofilter}</div>
{/if}

{if $products}
{assign var="layouts" value=""|fn_get_products_views:false:0}
{if $category_data.product_columns}
    {assign var="product_columns" value=$category_data.product_columns}
{else}
    {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
{/if}

{if $layouts.$selected_layout.template}
    {include file="`$layouts.$selected_layout.template`" columns=$product_columns}
{/if}

{elseif !$subcategories || $show_no_products_block}
<p class="ty-no-items cm-pagination-container">{__("text_no_products")}</p>
{else}
<div class="cm-pagination-container"></div>
{/if}
<!--category_products_{$block.block_id}--></div>

{capture name="mainbox_title"}<span {live_edit name="category:category:{$category_data.category_id}"}>{$category_data.category}</span>{/capture}
{/hook}
