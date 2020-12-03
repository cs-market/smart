{assign var="callback_url" value="payment_notification.notify?payment=psbankpayment"|fn_url:'C'}
<p>{__("psbankpayment_url_notice", ["[psbankpayment_url]" => $callback_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="psbankpayment_merchant">{__("psbankpayment_merchant")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant]" id="psbankpayment_merchant" value="{$processor_params.merchant}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psbankpayment_terminal">{__("psbankpayment_terminal")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][terminal]" id="psbankpayment_terminal" value="{$processor_params.terminal}"  size="60">
    </div>
</div>


<div class="control-group">
    <label class="control-label" for="psbankpayment_key">{__("psbankpayment_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][key]" id="psbankpayment_key" value="{$processor_params.key}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psbankpayment_merch_name">Merchant name:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merch_name]" id="psbankpayment_merch_name" value="{$processor_params.merch_name}"  size="60">
    </div>
</div>


<div class="control-group">
    <label class="control-label" for="psbankpayment_trtype">{__("psbankpayment_trtype")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][trtype]" id="psbankpayment_trtype">
            <option value="1" {if $processor_params.trtype == "1"}selected="selected"{/if}>{__("psbankpayment_trtype_1")}</option>
            <option value="12" {if $processor_params.trtype == "12"}selected="selected"{/if}>{__("psbankpayment_trtype_12")}</option>
        </select>
    </div>
</div>


<div class="control-group">
    <label class="control-label" for="psbankpayment_test">{__("psbankpayment_test")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="psbankpayment_test">
            <option value="0" {if $processor_params.test == "0"}selected="selected"{/if}>{__("psbankpayment_test_off")}</option>
            <option value="1" {if $processor_params.test == "1"}selected="selected"{/if}>{__("psbankpayment_test_on")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="psbankpayment_notify">{__("psbankpayment_notify")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][notify]" id="psbankpayment_notify">
            <option value="0" {if $processor_params.notify == "0"}selected="selected"{/if}>{__("psbankpayment_notify_off")}</option>
            <option value="1" {if $processor_params.notify == "1"}selected="selected"{/if}>{__("psbankpayment_notify_on")}</option>
        </select>
    </div>
</div>
    {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
<div class="control-group">
    <label class="control-label" for="elm_psbankpayment_status_success">{__("psbankpayment_status_success")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][status_success]" id="elm_psbankpayment_status_success">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.status_success) && $processor_params.status_success == $k) || (!isset($processor_params.status_success) && $k == 'P')}selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_psbankpayment_status_preauth">{__("psbankpayment_status_preauth")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][status_preauth]" id="elm_psbankpayment_status_preauth">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.status_preauth) && $processor_params.status_preauth == $k) || (!isset($processor_params.status_preauth) && $k == 'P')}selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>

