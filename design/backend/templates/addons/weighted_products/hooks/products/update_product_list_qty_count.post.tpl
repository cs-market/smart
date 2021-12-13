<div class="control-group">
    <label class="control-label" for="elm_is_weighted">{__("is_weighted")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="product_data[is_weighted]" value="N">
            <input type="checkbox" name="product_data[is_weighted]" id="elm_is_weighted" value="Y" {if $product_data.is_weighted == "YesNo::YES"|enum}checked="checked" {/if}>
        </label>
    </div>
</div>
