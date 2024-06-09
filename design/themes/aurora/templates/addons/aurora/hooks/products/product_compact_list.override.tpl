{hook name="products:product_compact_list"}
    <div class="ty-compact-list__item">
        <form {if !$config.tweaks.disable_dhtml}class="cm-ajax cm-ajax-full-render"{/if} action="{""|fn_url}" method="post" name="short_list_form{$obj_prefix}">
            <input type="hidden" name="result_ids" value="cart_status*,wish_list*,account_info*" />
            <input type="hidden" name="redirect_url" value="{$config.current_url}" />
            <div class="ty-compact-list__content">
                {hook name="products:product_compact_list_image"}
                    <div class="ty-compact-list__image">
                        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">
                            {include file="common/image.tpl" image_width=$image_width image_height=$image_height images=$product.main_pair obj_id=$obj_id_prefix}
                        </a>
                        {assign var="product_labels" value="product_labels_`$obj_prefix``$obj_id`"}
                        {$smarty.capture.$product_labels nofilter}
                    </div>
                {/hook}

                <div class="ty-compact-list__title">
                    {assign var="name" value="name_$obj_id"}<bdi>{$smarty.capture.$name nofilter}</bdi>
                    {* Change sku to amount status*}
                    {assign var="product_amount" value="product_amount_`$obj_id`"}
                    {if $smarty.capture.$product_amount|trim}
                        <div class="ty-grid-list__product_amount">
                            {$smarty.capture.$product_amount nofilter}
                        </div>
                    {/if}
                    {* end *}

                </div>

                <div class="ty-compact-list__controls">
                    <div class="ty-compact-list__price">
                        {assign var="old_price" value="old_price_`$obj_id`"}
                        {if $smarty.capture.$old_price|trim}
                            {$smarty.capture.$old_price nofilter}
                        {/if}

                        {assign var="price" value="price_`$obj_id`"}
                        {$smarty.capture.$price nofilter}

                        {assign var="clean_price" value="clean_price_`$obj_id`"}
                        {$smarty.capture.$clean_price nofilter}
                    </div>

                    {if !$smarty.capture.capt_options_vs_qty}
                        {assign var="product_options" value="product_options_`$obj_id`"}
                        {$smarty.capture.$product_options nofilter}

                        {assign var="qty" value="qty_`$obj_id`"}
                        {$smarty.capture.$qty nofilter}
                    {/if}

                    {if $show_add_to_cart}
                        {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                        {$smarty.capture.$add_to_cart nofilter}
                    {/if}
                </div>
            </div>
        </form>
    </div>
{/hook}