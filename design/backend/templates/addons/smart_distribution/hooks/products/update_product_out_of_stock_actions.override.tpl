{hook name="products:update_product_out_of_stock_actions"}
<div class="control-group">
    <label class="control-label" for="elm_out_of_stock_actions">{__("out_of_stock_actions")}:</label>
    <div class="controls">
        <select class="span3" name="product_data[out_of_stock_actions]" id="elm_out_of_stock_actions">
            <option value="N" {if $product_data.out_of_stock_actions == "N"}selected="selected"{/if}>{__("text_out_of_stock")}</option>
            <option value="B" {if $product_data.out_of_stock_actions == "B"}selected="selected"{/if}>{__("buy_in_advance")}</option>
            <option value="S" {if $product_data.out_of_stock_actions == "S"}selected="selected"{/if}>{__("sign_up_for_notification")}</option>
        </select>
    </div>
</div>
{/hook}