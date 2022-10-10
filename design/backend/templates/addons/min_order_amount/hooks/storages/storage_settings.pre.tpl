<div class="control-group">
    <label for="storage_min_order_{$id}" class="control-label">{__("storages.min_order_amount")}:</label>
    <div class="controls">
        <input type="text" size="70" id="storage_min_order_{$id}" name="storage_data[min_order_amount]" value="{$storage.min_order_amount|default:'0'}" class="input-large">
    </div>
</div>
<div class="control-group">
    <label for="min_order_weight_{$id}" class="control-label">{__("min_order_amount.min_order_weight")}:</label>
    <div class="controls">
        <input type="text" size="70" id="min_order_weight_{$id}" name="storage_data[min_order_weight]" value="{$storage.min_order_weight|default:'0'}" class="input-large">
    </div>
</div>
