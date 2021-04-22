<div class="control-group">
    <label class="control-label" for="elm_qty_step">{__("quantity_step")}:</label>
    <div class="controls">
        <input type="text" {*data-v-min="0" data-m-dec="1" data-a-sep=""*} name="product_data[qty_step]" id="elm_qty_step" value="{$product_data.qty_step|default:"0"}" class="input-small cm-numeric" />
    </div>
</div>