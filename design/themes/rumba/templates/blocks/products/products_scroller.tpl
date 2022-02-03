{** block-description:tmpl_scroller **}

{if $settings.Appearance.enable_quick_view == "Y" && $block.properties.enable_quick_view == "Y"}
    {$quick_nav_ids = $items|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}

{if $block.properties.hide_add_to_cart_button == "Y"}
        {$_show_add_to_cart=false}
    {else}
        {$_show_add_to_cart=true}
    {/if}
    {if $block.properties.show_price == "Y"}
        {$_hide_price=false}
    {else}
        {$_hide_price=true}
{/if}

{$show_old_price = true}

{$obj_prefix="`$block.block_id`000"}
{$block.block_id = "{$block.block_id}_{uniqid()}"}

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

<div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list ty-grid-list">
    {include file="blocks/product_list_templates/products_multicolumns.tpl" item_class='ty-scroller-list__item' no_pagination=true no_sorting=true products=$items show_add_to_cart=$_show_add_to_cart show_list_buttons=$_show_add_to_cart}
</div>

{include file="common/scroller_init.tpl" prev_selector="#owl_prev_`$obj_prefix`" next_selector="#owl_next_`$obj_prefix`"}
