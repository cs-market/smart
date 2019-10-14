<div id="exim_1c_extended" class="in collapse">
    <div class="control-group">
        <label for="product_send_price_1c" class="control-label">{__("1c.send_price_1c")}:</label>
        <div class="controls">
            <input type="hidden" name="product_data[send_price_1c]" value="N" />
            <input type="checkbox" name="product_data[send_price_1c]" id="product_send_price_1c" value="Y" {if $product_data.send_price_1c == "Y" || $runtime.mode == "add"}checked="checked"{/if} />
        </div>
    </div>
</div>