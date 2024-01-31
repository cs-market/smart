{hook name="products:product_amount"}
{if $show_product_amount && $product.is_edp != "Y" && $settings.General.inventory_tracking !== "YesNo::NO"|enum}
    <div class="cm-reload-{$obj_prefix}{$obj_id} stock-wrap" id="product_amount_update_{$obj_prefix}{$obj_id}">
        <input type="hidden" name="appearance[show_product_amount]" value="{$show_product_amount}" />
        {if !$product.hide_stock_info}
            {if $settings.Appearance.in_stock_field == "Y"}
                {if $product.tracking != "ProductTracking::DO_NOT_TRACK"|enum}
                    {if ($product_amount > 0 && $product_amount >= $product.min_qty) && $settings.General.inventory_tracking !== "YesNo::NO"|enum || $details_page}
                        {if (
                                $product_amount > 0
                                && $product_amount >= $product.min_qty
                                || $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum
                            )
                            && $settings.General.inventory_tracking !== "YesNo::NO"|enum
                        }
                            <div class="ty-control-group product-list-field">
                                {if $show_amount_label}
                                    <label class="ty-control-group__label">{__("availability")}:</label>
                                {/if}
                                <span id="qty_in_stock_{$obj_prefix}{$obj_id}" class="ty-qty-in-stock ty-control-group__item {if $product_amount <= 0}ty-qty-backorder{/if}">
                                    {if $product_amount > 0}
                                        {$product_amount}&nbsp;{__("items")}
                                    {else}
                                        {__("on_backorder")}
                                    {/if}
                                </span>
                            </div>
                        {elseif $settings.General.inventory_tracking !== "YesNo::NO"|enum && $settings.General.allow_negative_amount != "Y"}
                            <div class="ty-control-group product-list-field">
                                {if $show_amount_label}
                                    <label class="ty-control-group__label">{__("in_stock")}:</label>
                                {/if}
                                <span class="ty-qty-out-of-stock ty-control-group__item">{$out_of_stock_text}</span>
                            </div>
                        {/if}
                    {/if}
                {/if}
            {else}
                {if (
                        $product_amount > 0
                        && $product_amount >= $product.min_qty
                        || $product.tracking == "ProductTracking::DO_NOT_TRACK"|enum
                    )
                    && $settings.General.inventory_tracking !== "YesNo::NO"|enum
                    && $settings.General.allow_negative_amount !== "YesNo::YES"|enum
                    || $settings.General.inventory_tracking !== "YesNo::NO"|enum
                    && (
                        $settings.General.allow_negative_amount === "YesNo::YES"|enum
                        || $product.out_of_stock_actions == "OutOfStockActions::BUY_IN_ADVANCE"|enum
                    )
                }
                    <div class="ty-control-group product-list-field">
                        {if $show_amount_label}
                            <label class="ty-control-group__label">{__("availability")}:</label>
                        {/if}
                        <span class="ty-qty-in-stock ty-control-group__item {if $product_amount <= 0 || $product_amount/$product.qty_step <= 3}ty-qty-backorder{/if}" id="in_stock_info_{$obj_prefix}{$obj_id}">
                            {if $product_amount >= $product.qty_step}
                                {if $product_amount/$product.qty_step > 3}
                                    {__("in_stock")}
                                {else}
                                    {__("low_stock")}
                                {/if}
                            {else}
                                {__("on_backorder")}
                            {/if}
                        </span>
                    </div>
                {elseif $details_page
                    && (
                        $product_amount <= 0
                        || $product_amount < $product.min_qty
                    )
                    && $settings.General.inventory_tracking !== "YesNo::NO"|enum
                    && $settings.General.allow_negative_amount != "Y"
                }
                    <div class="ty-control-group product-list-field">
                        {if $show_amount_label}
                            <label class="ty-control-group__label">{__("availability")}:</label>
                        {/if}
                        <span class="ty-qty-out-of-stock ty-control-group__item" id="out_of_stock_info_{$obj_prefix}{$obj_id}">{$out_of_stock_text}</span>
                    </div>
                {/if}
            {/if}
        {/if}
    <!--product_amount_update_{$obj_prefix}{$obj_id}--></div>
{/if}
{/hook}
