{include file="common/subheader.tpl" title=__("invoice_for_payment")}

{$fields = [
    bank_name,
    inn,
    kpp,
    bank_recipient,
    bik,
    kor,
    rs
]}

{foreach from=$fields item=field}
    <div class="control-group">
        <label for="elm_company_invoice_for_payment_{$field}" class="control-label">{__("invoice_for_payment_$field")}:</label>
        <div class="controls">
            <input type="text" name="company_data[invoice_for_payment][{$field}]" id="elm_company_invoice_for_payment_{$field}" size="32" value="{$company_data.invoice_for_payment.$field}" class="input-large" />
        </div>
    </div>
{/foreach}
