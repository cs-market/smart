{assign var="product_detail_view_url" value="products.view?product_id=`$product.product_id`"}
{capture name="product_detail_view_url"}
{** Sets product detail view link *}
{hook name="products:product_detail_view_url"}
{$product_detail_view_url}
{/hook}
{/capture}

{$product_detail_view_url = $smarty.capture.product_detail_view_url|trim}

{capture name="main_icon"}
    <a href="{"$product_detail_view_url"|fn_url}">
        {include file="common/image.tpl" obj_id=$obj_id_prefix images=$product.main_pair image_width=$image_width|default:$settings.Thumbnails.product_lists_thumbnail_width image_height=$image_height|default:$settings.Thumbnails.product_lists_thumbnail_height}
    </a>
{/capture}

{if $product.image_pairs && $show_gallery}
<div class="ty-center-block">
    {if $addons.azure_theme.grid_images == 'gallery'}
    <div class="ty-thumbs-wrapper owl-carousel cm-image-gallery" data-ca-items-count="1" id="icons_{$obj_id_prefix}">
        {if $product.main_pair}
            <div class="cm-gallery-item cm-item-gallery">
                {$smarty.capture.main_icon nofilter}
            </div>
        {/if}
        {foreach from=$product.image_pairs item="image_pair"}
            {if $image_pair}
                <div class="cm-gallery-item cm-item-gallery">
                    <a href="{"$product_detail_view_url"|fn_url}">
                        {include file="common/image.tpl" no_ids=true images=$image_pair iimage_width=$image_width|default:$settings.Thumbnails.product_lists_thumbnail_width image_height=$image_height|default:$settings.Thumbnails.product_lists_thumbnail_height lazy_load=true}
                    </a>
                </div>
            {/if}
        {/foreach}
    </div>
    {elseif $addons.azure_theme.grid_images == 'flip'}
        <div class="ty-flip-wrapper">
        {if $product.main_pair}
            <div class="cm-flip-item">
                {$smarty.capture.main_icon nofilter}
            </div>
        {/if}
        {foreach from=$product.image_pairs item="image_pair"}
            {if $image_pair && !$first}
                <div class="cm-flip-item">
                    <a href="{"$product_detail_view_url"|fn_url}">
                        {include file="common/image.tpl" no_ids=true images=$image_pair image_width=$image_width|default:$settings.Thumbnails.product_lists_thumbnail_width image_height=$image_height|default:$settings.Thumbnails.product_lists_thumbnail_height lazy_load=false}
                    </a>
                </div>
                {$first = true}
            {/if}
        {/foreach}
        </div>
    {/if}
</div>
{else}
    {$smarty.capture.main_icon nofilter}
{/if}