{if $user_type == 'C'}
<div class="control-group">
    <label class="control-label" for="elm_min_order_amount_{$id}">{__('min_order_amount')}</label>
    <div class="controls">
        <input type="text" id="elm_min_order_amount_{$id}" name="user_data[min_order_amount]" size="35" value="{$user_data.min_order_amount}" class="input-small">
    </div>
</div>
{/if}
