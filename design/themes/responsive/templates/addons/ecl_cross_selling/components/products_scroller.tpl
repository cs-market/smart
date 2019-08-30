{** block-description:tmpl_scroller **}

{if $block.properties.enable_quick_view == "Y"}
    {$quick_nav_ids = $items|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}

{if $block.properties.hide_add_to_cart_button == "Y"}
    {assign var="_show_add_to_cart" value=false}
{else}
    {assign var="_show_add_to_cart" value=true}
{/if}
{if $block.properties.show_price == "Y"}
    {assign var="_hide_price" value=false}
{else}
    {assign var="_hide_price" value=true}
{/if}
{if !$image_width_related_product}
    {assign var="image_width_related_product" value=120}
{/if}

{assign var="obj_prefix" value="`$block.block_id`000"}

{if $block.properties.outside_navigation == "Y"}
    <div class="owl-theme ty-owl-controls">
        <div class="owl-controls clickable owl-controls-outside"  id="owl_outside_nav_{$block.block_id}">
            <div class="owl-buttons">
                <div id="owl_prev_{$obj_prefix}" class="owl-prev"><i class="ty-icon-left-open-thin"></i></div>
                <div id="owl_next_{$obj_prefix}" class="owl-next"><i class="ty-icon-right-open-thin"></i></div>
            </div>
        </div>
    </div>
{/if}

<div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list {$related_product_class}">
    {foreach from=$items item="product" name="for_products"}
        {hook name="products:product_scroller_list"}
            <div class="ty-scroller-list__item">
                {assign var="obj_id" value="scr_`$block.block_id`000`$product.product_id`"}
                <div class="ty-scroller-list__img-block">
                    {include file="common/image.tpl" assign="object_img" images=$product.main_pair image_width=$image_width_related_product image_height=$image_width_related_product no_ids=true lazy_load=true}
                    <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$object_img nofilter}</a>
                    {if $block.properties.enable_quick_view == "Y"}
                        {include file="views/products/components/quick_view_link.tpl" quick_nav_ids=$quick_nav_ids}
                    {/if}
                </div>
                <div class="ty-scroller-list__description">
                    {strip}
                        {include file="blocks/list_templates/simple_list.tpl" product=$product show_trunc_name=true show_price=true show_add_to_cart=$_show_add_to_cart but_role="action" hide_price=$_hide_price hide_qty=true show_discount_label=true hide_form=true}
                    {/strip}
                </div>
            </div>
        {/hook}
    {/foreach}
</div>


{include file="addons/ecl_cross_selling/components/scroller_init_notification.tpl" prev_selector="#owl_prev_`$obj_prefix`" next_selector="#owl_next_`$obj_prefix`"}

