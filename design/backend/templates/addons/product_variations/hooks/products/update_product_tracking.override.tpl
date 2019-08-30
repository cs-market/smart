{if $product_type->isFieldAvailable("tracking")}
    <div class="control-group">
        <label class="control-label" for="elm_product_tracking">{__("inventory")}:</label>
        <div class="controls">
            <select class="span5" name="product_data[tracking]" id="elm_product_tracking" {if $settings.General.inventory_tracking == "N"}disabled="disabled"{/if}>
                {if $product_data.product_type === "\Tygh\Addons\ProductVariations\Product\Manager::PRODUCT_TYPE_CONFIGURABLE"|constant}
                    {$track = __("track")}
                {else}
                    {if $product_options}
                        <option value="{"ProductTracking::TRACK_WITH_OPTIONS"|enum}" {if $product_data.tracking == "ProductTracking::TRACK_WITH_OPTIONS"|enum && $settings.General.inventory_tracking == "Y"}selected="selected"{/if}>{__("track_with_options")}</option>
                    {/if}
                    {$track = __("track_without_options")}
                {/if}
                <option value="{"ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}" {if $product_data.tracking == "{"ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}" && $settings.General.inventory_tracking == "Y"}selected="selected"{/if}>{$track}</option>
                <option value="{"ProductTracking::DO_NOT_TRACK"|enum}" {if $product_data.tracking == "{"ProductTracking::DO_NOT_TRACK"|enum}" || $settings.General.inventory_tracking == "N"}selected="selected"{/if}>{__("dont_track")}</option>
            </select>
        </div>
    </div>
{else}
    <!-- Overridden by the Product Variations add-on -->
{/if}
