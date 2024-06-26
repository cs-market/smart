{if $products}
    {script src="js/tygh/exceptions.js"}
    {if $addons.aurora.dynamic_quantity == "YesNo::YES"|enum}{script src="js/addons/aurora/cart_content.js"}{/if}

    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}

    {if !$no_sorting}
        {include file="views/products/components/sorting.tpl"}
    {/if}

    {if $item_number == "Y"}
        {assign var="cur_number" value=1}
    {/if}

    {* FIXME: Don't move this file *}
    {script src="js/addons/aurora/product_image_gallery.js"}

    {if $settings.Appearance.enable_quick_view == 'Y'}
        {$quick_nav_ids = $products|fn_fields_from_multi_level:"product_id":"product_id"}
    {/if}
    {if !$show_empty && $products|count < $settings.Appearance.columns_in_products_list}
        {$grid_columns = $products|count}
    {elseif $columns}
        {$grid_columns = $columns}
    {/if}

    {$wrapper_class=$wrapper_class|default:"ty-grid-list"}

    {if !$item_class}<div class="{$wrapper_class} " {if $grid_columns}style="--grid-columns: {$grid_columns}"{/if}>{/if}
        {strip}
            {foreach from=$products item="product" name="sproducts"}
                {if $product}
                    {assign var="obj_id" value=$product.product_id}
                    {assign var="obj_id_prefix" value="`$obj_prefix``$product.product_id`"}
                    {$wishlist_but_meta = 'ty-btn-icon ty-btn__add-to-wish'}
                    {if $addons.aurora.inline_controls == "YesNo::NO"|enum}
                        {$active_class = 'ty-btn__full-width'}
                    {else}
                        {$but_text=" "}
                    {/if}

                    {include file="common/product_data.tpl" product=$product show_amount_label=false }
                    
                    <div class="ty-grid-list__item 
                        {if $product.dynamic_quantity == "YesNo::YES"|enum} ty-dynamic-quantity {/if}
                        {if $product.in_cart} ty-product-in-cart {/if}
                        {if $product.is_weighted == "YesNo::YES"|enum} ty-weighted-product {/if}
                        {if $addons.aurora.inline_controls == "YesNo::YES"|enum} ty-grid-list__inline-controls {/if}
                        {$item_class}
                        {if $settings.Appearance.enable_quick_view == "YesNo::YES"|enum || $show_features} ty-grid-list__item--overlay{/if}">

                        {assign var="form_open" value="form_open_`$obj_id`"}
                        {$smarty.capture.$form_open nofilter}
                            {hook name="products:product_multicolumns_list"}
                                <div class="ty-grid-list__image">
                                    {include file="views/products/components/product_icon.tpl" product=$product show_gallery=true}

                                    {assign var="product_labels" value="product_labels_`$obj_prefix``$obj_id`"}
                                    {$smarty.capture.$product_labels nofilter}
                                </div>

                                {assign var="product_amount" value="product_amount_`$obj_id`"}
                                {if $smarty.capture.$product_amount|trim}
                                    <div class="ty-grid-list__product_amount">
                                        {$smarty.capture.$product_amount nofilter}
                                    </div>
                                {/if}

                                <div class="ty-grid-list__item-name">
                                    {if $item_number == "Y"}
                                        <span class="item-number">{$cur_number}.&nbsp;</span>
                                        {math equation="num + 1" num=$cur_number assign="cur_number"}
                                    {/if}

                                    {assign var="name" value="name_$obj_id"}
                                    <bdi>{$smarty.capture.$name nofilter}</bdi>
                                </div>

                                {assign var="rating" value="rating_$obj_id"}
                                {if $smarty.capture.$rating}
                                    <div class="grid-list__rating">
                                        {$smarty.capture.$rating nofilter}
                                    </div>
                                {/if}

                                {assign var="sku" value="sku_$obj_id"}
                                {if $smarty.capture.$sku|trim}
                                    <div class="ty-grid-list__sku">
                                        {$smarty.capture.$sku nofilter}
                                    </div>
                                {/if}

                                {if $show_price}
                                <div class="ty-grid-list__price {if $product.price == 0}ty-grid-list__no-price{/if}">
                                    {hook name="products:grid_prices_block"}
                                        {assign var="old_price" value="old_price_`$obj_id`"}
                                        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

                                        {assign var="price" value="price_`$obj_id`"}
                                        {$smarty.capture.$price nofilter}

                                        {assign var="clean_price" value="clean_price_`$obj_id`"}
                                        {$smarty.capture.$clean_price nofilter}

                                        {assign var="list_discount" value="list_discount_`$obj_id`"}
                                        {$smarty.capture.$list_discount nofilter}
                                    {/hook}
                                </div>
                                {/if}

                                {capture name="product_multicolumns_list_control_data_wrapper"}
                                    <div class="ty-grid-list__controls cm-product-controls">
                                        {hook name="products:product_multicolumns_list_control_data_wrapper"}
                                        {assign var="qty" value="qty_`$obj_id`"}
                                        {if $smarty.capture.$qty|trim}
                                            <div class="ty-grid-list__qty {if $product.in_cart == "YesNo::YES"|enum}ty-cart-content__qty{/if}">
                                                {$smarty.capture.$qty nofilter}
                                            </div>
                                            {include file="buttons/update_cart.tpl"
                                                 but_id="button_cart_`$obj_id`"
                                                 but_meta="ty-btn--recalculate-cart hidden hidden-phone hidden-tablet"
                                                 but_name="dispatch[checkout.update_qty]"
                                            }
                                        {/if}
                                        {capture name="product_multicolumns_list_control_data"}
                                            {hook name="products:product_multicolumns_list_control"}
                                                {if $settings.Appearance.enable_quick_view == 'Y'}
                                                    {include file="views/products/components/quick_view_link.tpl" quick_nav_ids=$quick_nav_ids}
                                                {/if}

                                                {if $show_add_to_cart}
                                                    <div class="button-container">
                                                        {$add_to_cart = "add_to_cart_`$obj_id`"}
                                                        {$smarty.capture.$add_to_cart nofilter}
                                                    </div>
                                                {/if}
                                            {/hook}
                                        {/capture}
                                        {$smarty.capture.product_multicolumns_list_control_data nofilter}
                                        {/hook}
                                    </div>
                                {/capture}

                                {if $smarty.capture.product_multicolumns_list_control_data|trim}
                                    {$smarty.capture.product_multicolumns_list_control_data_wrapper nofilter}
                                {/if}
                            {/hook}
                        {assign var="form_close" value="form_close_`$obj_id`"}
                        {$smarty.capture.$form_close nofilter}
                    </div>
                {/if}
            {/foreach}
            {if $show_empty}
                {assign var="iteration" value=$smarty.foreach.sproducts.iteration}
                {capture name="iteration"}{$iteration}{/capture}
                {hook name="products:products_multicolumns_extra"}
                {/hook}
                {assign var="iteration" value=$smarty.capture.iteration}
                {if $iteration % $columns != 0}
                    {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
                    {section loop=$empty_count name="empty_rows"}
                        <div class="ty-grid-list__item">
                            <div class="ty-product-empty">
                                <span class="ty-product-empty__text">{__("empty")}</span>
                            </div>
                        </div>
                    {/section}
                {/if}
            {/if}
        {/strip}
    {if !$item_class}</div>{/if}

    {if !$no_pagination}
        {include file="common/pagination.tpl"}
    {/if}

{/if}

{capture name="mainbox_title"}{$title}{/capture}
