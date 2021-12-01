<div class="control-group">
    <label class="control-label" for="sberbank_login">{__("addons.sberbank_fz.login")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][login]" id="sberbank_login" value="{$processor_params.login}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="sberbank_password">{__("addons.sberbank_fz.password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="sberbank_password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="two_staging">{__("addons.sberbank_fz.two_staging")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][two_staging]" id="two_staging">
            <option value="0"
                    {if $processor_params.two_staging == 0}selected="selected"{/if}>{__("addons.sberbank_fz.one-stage")}</option>
            <option value="1"
                    {if $processor_params.two_staging == 1}selected="selected"{/if}>{__("addons.sberbank_fz.two-stage")}</option>
        </select>
    </div>
</div>

{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

<div class="control-group">
    <label class="control-label" for="confirmed_order_status">{__("addons.sberbank_fz.confirmed_order_status")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][confirmed_order_status]" id="confirmed_order_status">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}"
                        {if $processor_params.confirmed_order_status == $k || !$processor_params.confirmed_order_status && $k == 'P'}selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="logging">{__("addons.sberbank_fz.logging")}:</label>
    <div class="controls">
        <input type="checkbox" name="payment_data[processor_params][logging]" id="logging" value="Y" {if $processor_params.logging == 'Y'} checked="checked"{/if}/>
    </div>
</div>

{include file="common/subheader.tpl" title=__("addons.sberbank_fz.text_ofd_map") target="#text_ofd_map"}
<div id="text_ofd_map" class="in collapse">

    <div class="control-group">
        <label class="control-label" for="send_order">{__("addons.sberbank_fz.send_order")}:</label>
        <div class="controls">
            <input type="checkbox" name="payment_data[processor_params][send_order]" id="send_order"
                   value="Y" {if $processor_params.send_order == 'Y'} checked="checked"{/if}/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="mode">{__("payment_method")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][payment_method]" id="payment_method">
                <option value="regular" {if $processor_params.payment_method == "regular"}selected="selected"{/if}>{__("addons.sberbank_fz.regular")}</option>
                <option value="credit" {if $processor_params.payment_method == "credit"}selected="selected"{/if}>{__("addons.sberbank_fz.credit")}</option>
                <option value="installment" {if $processor_params.payment_method == "installment"}selected="selected"{/if}>{__("addons.sberbank_fz.installment")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="tax_system">{__("addons.sberbank_fz.tax_system")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][tax_system]" id="tax_system">
                <option value="0" {if $processor_params.tax_system == 0}selected="selected"{/if}>Общая</option>
                <option value="1" {if $processor_params.tax_system == 1}selected="selected"{/if}>Упрощённая, доход
                </option>
                <option value="2" {if $processor_params.tax_system == 2}selected="selected"{/if}>Упрощённая, доход
                    минус расход
                </option>
                <option value="3" {if $processor_params.tax_system == 3}selected="selected"{/if}>Единый налог на
                    вменённый доход
                </option>
                <option value="4" {if $processor_params.tax_system == 4}selected="selected"{/if}>Единый
                    сельскохозяйственный налог
                </option>
                <option value="5" {if $processor_params.tax_system == 5}selected="selected"{/if}>Патентная система
                    налогообложения
                </option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="tax_type">{__("addons.sberbank_fz.tax_type")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][tax_type]" id="tax_type">
                <option value="0" {if $processor_params.tax_type == 0}selected="selected"{/if}>Без НДС</option>
                <option value="1" {if $processor_params.tax_type == 1}selected="selected"{/if}>НДС по ставке 0%
                </option>
                <option value="2" {if $processor_params.tax_type == 2}selected="selected"{/if}>НДС чека по ставке
                    10%
                </option>
                <option value="3" {if $processor_params.tax_type == 3}selected="selected"{/if}>НДС чека по ставке
                    18%
                </option>
                <option value="6" {if $processor_params.tax_type == 6}selected="selected"{/if}>НДС чека по расчетной
                    ставке 20%
                </option>
                <option value="4" {if $processor_params.tax_type == 4}selected="selected"{/if}>НДС чека по расчетной
                    ставке 10/110
                </option>
                <option value="5" {if $processor_params.tax_type == 5}selected="selected"{/if}>НДС чека по расчетной
                    ставке 10/118
                </option>
                <option value="7" {if $processor_params.tax_type == 7}selected="selected"{/if}>НДС чека по расчетной
                    ставке 20/120
                </option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ffd_version">{__("addons.sberbank_fz.ffd_version")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][ffd_version]" id="ffd_version">
                <option value="v10" {if $processor_params.ffd_version == "v10"}selected="selected"{/if}>ФФД 1.0</option>
                <option value="v105" {if $processor_params.ffd_version == "v105"}selected="selected"{/if}>ФФД 1.05</option>
                {*<option value="v11" {if $processor_params.ffd_version == "v11"}selected="selected"{/if}>ФФД 1.1</option>*}
            </select>
            <p class="description">
                <small>Формат версии требуется указать в личном кабинете банка и в кабинете сервиса фискализации</small>
            </p>
        </div>
    </div>


    <div class="control-group">
        <label class="control-label" for="ffd_paymentMethodType">{__("addons.sberbank_fz.ffd_payment_method_type")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][ffd_paymentMethodType]" id="ffd_paymentMethodType">
                <option value="1" {if $processor_params.ffd_paymentMethodType == 1}selected="selected"{/if}>Полная предварительная оплата до момента передачи предмета расчёта</option>
                <option value="2" {if $processor_params.ffd_paymentMethodType == 2}selected="selected"{/if}>Частичная предварительная оплата до момента передачи предмета расчёта</option>
                <option value="3" {if $processor_params.ffd_paymentMethodType == 3}selected="selected"{/if}>Аванс</option>
                <option value="4" {if $processor_params.ffd_paymentMethodType == 4}selected="selected"{/if}>Полная оплата в момент передачи предмета расчёта</option>
                <option value="5" {if $processor_params.ffd_paymentMethodType == 5}selected="selected"{/if}>Частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит</option>
                <option value="6" {if $processor_params.ffd_paymentMethodType == 6}selected="selected"{/if}>Передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит</option>
                <option value="7" {if $processor_params.ffd_paymentMethodType == 7}selected="selected"{/if}>Оплата предмета расчёта после его передачи с оплатой в кредит</option>
            </select>
            <p class="description">
                <small>Используется в версих ФФД, начиная с 1.05</small>
            </p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ffd_paymentObjectType">{__("addons.sberbank_fz.ffd_payment_object_type")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][ffd_paymentObjectType]" id="ffd_paymentObjectType">
                <option value="1" {if $processor_params.ffd_paymentObjectType == 1}selected="selected"{/if}>Товар</option>
                <option value="2" {if $processor_params.ffd_paymentObjectType == 2}selected="selected"{/if}>Подакцизный товар</option>
                <option value="3" {if $processor_params.ffd_paymentObjectType == 3}selected="selected"{/if}>Работа</option>
                <option value="4" {if $processor_params.ffd_paymentObjectType == 4}selected="selected"{/if}>Услуга</option>
                <option value="5" {if $processor_params.ffd_paymentObjectType == 5}selected="selected"{/if}>Ставка азартной игры</option>
                <!--<option value="6">Выигрыш азартной игры</option>-->
                <option value="7" {if $processor_params.ffd_paymentObjectType == 7}selected="selected"{/if}>Лотерейный билет</option>
                <!--<option value="8">Выигрыш лотереи</option>-->
                <option value="9" {if $processor_params.ffd_paymentObjectType == 9}selected="selected"{/if}>Предоставление РИД</option>
                <option value="10" {if $processor_params.ffd_paymentObjectType == 10}selected="selected"{/if}>Платёж</option>
                <option value="11" {if $processor_params.ffd_paymentObjectType == 11}selected="selected"{/if}>Агентское вознаграждение</option>
                <option value="12" {if $processor_params.ffd_paymentObjectType == 12}selected="selected"{/if}>Составной предмет расчёта</option>
                <option value="13" {if $processor_params.ffd_paymentObjectType == 13}selected="selected"{/if}>Иной предмет расчёта</option>
            </select>
            <p class="description">
                <small>Используется в версих ФФД, начиная с 1.05</small>
            </p>
        </div>
    </div>
</div>
