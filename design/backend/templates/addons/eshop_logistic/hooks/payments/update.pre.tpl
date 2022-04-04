{$eshop_payment_types = ''|fn_eshop_logistic_get_eshop_payment_types}
<div class="control-group">
    <label class="control-label" for="elm_eshop_payment_type_{$id}">{__("eshop_logistic.payment_type")}:</label>
    <div class="controls">
        <select id="elm_eshop_payment_type_{$id}" name="payment_data[eshop_payment_type]">
            {foreach $eshop_payment_types as $type => $eshop_payment_data}
                <option value="{$type}" {if $payment.eshop_payment_type == $type}selected="selected"{/if}>{$eshop_payment_data.description}</option>
            {/foreach}
        </select>
        <p class="muted description">{__("eshop_logistic.tt_payment_type")}</p>
    </div>
</div>