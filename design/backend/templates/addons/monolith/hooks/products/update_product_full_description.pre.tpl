<div class="control-group {$no_hide_input_if_shared_product}">
    <label for="elm_price_price" class="control-label">{__("subbrand")}:</label>
    <div class="controls">
        <input type="text" name="product_data[subbrand]" id="elm_subbrand" size="10" value="{$product_data.subbrand}" class="input-long"/>
        {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id="price" name="update_all_vendors[price]"}
    </div>
</div>
