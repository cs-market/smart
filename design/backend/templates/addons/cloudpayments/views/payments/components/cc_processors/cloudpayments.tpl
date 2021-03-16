{assign var="check_url" value="payment_notification.check?payment=cloudpayments"|fn_url:'C':'http'}
{assign var="pay_url" value="payment_notification.pay?payment=cloudpayments"|fn_url:'C':'http'}
{assign var="confirm_url" value="payment_notification.confirm?payment=cloudpayments"|fn_url:'C':'http'}
{assign var="fail_url" value="payment_notification.fail?payment=cloudpayments"|fn_url:'C':'http'}
{assign var="refund_url" value="payment_notification.refund?payment=cloudpayments"|fn_url:'C':'http'}
{assign var="cancel_url" value="payment_notification.cancel?payment=cloudpayments"|fn_url:'C':'http'}

<div>
    {__("cloudpayments_notify_url_notice", [
        "[check_url]" => $check_url,
        "[pay_url]" => $pay_url,
	    "[confirm_url]" => $confirm_url,
        "[fail_url]" => $fail_url,
        "[refund_url]" => $refund_url,
	    "[cancel_url]" => $cancel_url
    ])}
</div>
<hr>

<div class="control-group">
    <label class="control-label" for="cloudpayments_public_id">{__("cloudpayments_public_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][public_id]" id="cloudpayments_public_id" value="{$processor_params.public_id}" size="120">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_secret_key">{__("cloudpayments_secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret_key]" id="cloudpayments_secret_key" value="{$processor_params.secret_key}" size="120">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_language">{__("cloudpayments_language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language]" id="cloudpayments_language">
            <option value="ru-RU" {if $processor_params.language == "ru"}selected="selected"{/if}>Русский (MSK)</option>
            <option value="en-US" {if $processor_params.language == "en"}selected="selected"{/if}>Английский (CET)</option>
            <option value="lv" {if $processor_params.language == "lv"}selected="selected"{/if}>Латышский (CET)</option>
            <option value="az" {if $processor_params.language == "az"}selected="selected"{/if}>Азербайджанский (AZT)</option>
            <option value="kk" {if $processor_params.language == "kk"}selected="selected"{/if}>Русский (ALMT)</option>
            <option value="kk-KZ" {if $processor_params.language == "kk-KZ"}selected="selected"{/if}>Казахский (ALMT)</option>
            <option value="uk" {if $processor_params.language == "uk"}selected="selected"{/if}>Украинский (EET)</option>
            <option value="pl" {if $processor_params.language == "pl"}selected="selected"{/if}>Польский (CET)</option>
            <option value="pt" {if $processor_params.language == "pt"}selected="selected"{/if}>Португальский (CET)</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="cloudpayments_currency">
            <option value="RUB" {if $processor_params.currency == "RUB"}selected="selected"{/if}>Российский рубль </option>
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>Евро </option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>Доллар США </option>
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>Фунт стерлингов </option>
            <option value="UAH" {if $processor_params.currency == "UAH"}selected="selected"{/if}>Украинская гривна </option>
            <option value="BYN" {if $processor_params.currency == "BYN"}selected="selected"{/if}>Белорусский рубль </option>
            <option value="KZT" {if $processor_params.currency == "KZT"}selected="selected"{/if}>Казахский тенге </option>
            <option value="AZN" {if $processor_params.currency == "AZN"}selected="selected"{/if}>Азербайджанский манат </option>
            <option value="CHF" {if $processor_params.currency == "CHF"}selected="selected"{/if}>Швейцарский франк </option>
            <option value="CZK" {if $processor_params.currency == "CZK"}selected="selected"{/if}>Чешская крона </option>
            <option value="CAD" {if $processor_params.currency == "CAD"}selected="selected"{/if}>Канадский доллар </option>
            <option value="PLN" {if $processor_params.currency == "PLN"}selected="selected"{/if}>Польский злотый </option>
            <option value="SEK" {if $processor_params.currency == "SEK"}selected="selected"{/if}>Шведская крона </option>
            <option value="TRY" {if $processor_params.currency == "TRY"}selected="selected"{/if}>Турецкая лира </option>
            <option value="CNY" {if $processor_params.currency == "CNY"}selected="selected"{/if}>Китайский юань </option>
            <option value="INR" {if $processor_params.currency == "INR"}selected="selected"{/if}>Индийская рупия </option>
            <option value="BRL" {if $processor_params.currency == "BRL"}selected="selected"{/if}>Бразильский реал </option>
            <option value="ZAL" {if $processor_params.currency == "ZAL"}selected="selected"{/if}>Южноафриканский рэнд </option>
            <option value="UZS" {if $processor_params.currency == "UZS"}selected="selected"{/if}>Узбекский сум </option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_receipt">{__("cloudpayments_receipt")}:</label>
    <div class="controls">
        <input type="checkbox" name="payment_data[processor_params][receipt]" id="cloudpayments_receipt" value="Y" {if $processor_params.receipt == 'Y'} checked="checked"{/if}/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_payment_scheme">{__("cloudpayments_payment_scheme")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][payment_scheme]" id="cloudpayments_payment_scheme">
            <option value="charge" {if $processor_params.payment_scheme == "charge"}selected="selected"{/if}>{__("cloudpayments_payment_scheme_charge")}</option>
            <option value="auth" {if $processor_params.payment_scheme == "auth"}selected="selected"{/if}>{__("cloudpayments_payment_scheme_auth")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_skin">{__("cloudpayments_skin")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][skin]" id="cloudpayments_skin">
            <option value="classic" {if $processor_params.skin == "classic"}selected="selected"{/if}>{__("cloudpayments_skin_classic")}</option>
            <option value="modern" {if $processor_params.skin == "modern"}selected="selected"{/if}>{__("cloudpayments_skin_modern")}</option>
            <option value="mini" {if $processor_params.skin == "mini"}selected="selected"{/if}>{__("cloudpayments_skin_mini")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cloudpayments_taxation_system">{__("cloudpayments_taxation_system")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][taxation_system]" id="cloudpayments_taxation_system">
            <option value="0" {if $processor_params.taxation_system == "0"}selected="selected"{/if}>{__("cloudpayments_taxation_system_osn")}</option>
            <option value="1" {if $processor_params.taxation_system == "1"}selected="selected"{/if}>{__("cloudpayments_taxation_system_usn_income")}</option>
            <option value="2" {if $processor_params.taxation_system == "2"}selected="selected"{/if}>{__("cloudpayments_taxation_system_usn_income_outcome")}</option>
            <option value="3" {if $processor_params.taxation_system == "3"}selected="selected"{/if}>{__("cloudpayments_taxation_system_envd")}</option>
            <option value="4" {if $processor_params.taxation_system == "4"}selected="selected"{/if}>{__("cloudpayments_taxation_system_esn")}</option>
            <option value="5" {if $processor_params.taxation_system == "5"}selected="selected"{/if}>{__("cloudpayments_taxation_system_patent")}</option>
        </select>
    </div>
</div>

{include file="common/subheader.tpl" title=__("cloudpayments_text_status_map") target="#text_status_map"}

<div id="text_status_map" class="in collapse">
    {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

    <div class="control-group">
        <label class="control-label" for="cloudpayments_status_paid">{__("cloudpayments_status.paid")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][paid]" id="cloudpayments_status_paid">
                {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if (isset($processor_params.statuses.paid) && $processor_params.statuses.paid == $k) || (!isset($processor_params.statuses.paid) && $k == 'P')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="cloudpayments_status_confirm">{__("cloudpayments_status.confirm")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][confirm]" id="cloudpayments_status_confirm">
                {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if (isset($processor_params.statuses.confirm) && $processor_params.statuses.confirm == $k) || (!isset($processor_params.statuses.confirm) && $k == 'P')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="cloudpayments_status_failed">{__("cloudpayments_status.failed")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][failed]" id="cloudpayments_status_failed">
                {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if (isset($processor_params.statuses.failed) && $processor_params.statuses.failed == $k) || (!isset($processor_params.statuses.failed) && $k == 'F')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="cloudpayments_status_refunded">{__("cloudpayments_status.refunded")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][refunded]" id="cloudpayments_status_refunded">
                {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if (isset($processor_params.statuses.refunded) && $processor_params.statuses.refunded == $k) || (!isset($processor_params.statuses.refunded) && $k == 'E')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="cloudpayments_status_cancel">{__("cloudpayments_status.cancel")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][cancel]" id="cloudpayments_status_cancel">
                {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if (isset($processor_params.statuses.cancel) && $processor_params.statuses.cancel == $k) || (!isset($processor_params.statuses.cancel) && $k == 'D')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>
