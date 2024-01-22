<input type="hidden" name="shipping_data[service_params][configured]" value="Y" />
<input type="hidden" name="shipping_data[service_params][limit_weekday]" value="C">
{$disabled = "MULTIVENDOR"|fn_allowed_for && $shipping.service_params.depends_on_parent !="YesNo::NO"|enum}

<div class="control-group">
    <label class="control-label" for="elm_offer_documents">{__("calendar_delivery.offer_documents")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][offer_documents]" value="N">
        <input id="elm_offer_documents" type="checkbox" name="shipping_data[service_params][offer_documents]" value="Y" {if $shipping.service_params.offer_documents == "YesNo::YES"|enum}checked="_checked"{/if}>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="elm_offer_documents">{__("calendar_delivery.offer_documents_checked")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][offer_documents_checked]" value="N">
        <input id="elm_offer_documents" type="checkbox" name="shipping_data[service_params][offer_documents_checked]" value="Y" {if $shipping.service_params.offer_documents_checked == "YesNo::YES"|enum}checked="_checked"{/if}>
    </div>
</div>

{if "MULTIVENDOR"|fn_allowed_for}
<div class="control-group">
    <label class="control-label" for="elm_depends_on_parent">{__("calendar_delivery.settings_depends_on_parent")}</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][depends_on_parent]" value="N">
        <input id="elm_offer_documents" type="checkbox" name="shipping_data[service_params][depends_on_parent]" value="Y" {if $disabled}checked="_checked"{/if} onclick="Tygh.$.disable_elms(['shipping_nearest_delivery_today', 'shipping_nearest_delivery_tomorrow', 'shipping_nearest_delivery_aftertomorrow', 'shipping_nearest_delivery_other', 'shipping_nearest_delivery_other_input', 'elm_shipping_working_time_till', 'elm_shipping_max_date', 'elm_shipping_saturday_shipping', 'elm_shipping_sunday_shipping', 'elm_shipping_monday_rule', 'elm_shipping_period_start', 'elm_shipping_period_finish', 'elm_shipping_period_step'], this.checked);">
    </div>
</div>
{/if}

{include file="addons/calendar_delivery/components/nearest_delivery.tpl" disabled=$disabled id='shipping_nearest_delivery' name='shipping_data[service_params][nearest_delivery]' params=$shipping.service_params}

<div class="control-group">
    <label for="elm_shipping_working_time_till" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="__('working_time_till_error_message')">{__("calendar_delivery.working_time_till")}:</label>
    <div class="controls">
        <input class="input-mini cm-trim" id="elm_shipping_working_time_till" size="5" maxlength="5" type="text" name="shipping_data[service_params][working_time_till]" value="{$shipping.service_params.working_time_till}" placeholder="00:00" {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>

<div class="control-group">
    <label for="elm_shipping_max_date" class="cm-numeric control-label">{__("calendar_delivery.max_date")}:</label>
    <div class="controls">
        <input class="input-time cm-trim" id="elm_shipping_max_date" size="5" maxlength="5" type="text" name="shipping_data[service_params][max_date]" value="{$shipping.service_params.max_date}" placeholder="1" {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>

<div class="control-group">
    <label for="elm_shipping_saturday_shipping" class="control-label">{__("calendar_delivery.saturday_shipping")}:</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][saturday_shipping]" value="N">
        <input type="checkbox" name="shipping_data[service_params][saturday_shipping]" id="elm_shipping_saturday_shipping" value="Y" {if $shipping.service_params.saturday_shipping == 'Y'} checked="checked" {/if} {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>
<div class="control-group">
    <label for="elm_shipping_sunday_shipping" class="control-label">{__("calendar_delivery.sunday_shipping")}:</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][sunday_shipping]" value="N">
        <input type="checkbox" name="shipping_data[service_params][sunday_shipping]" id="elm_shipping_sunday_shipping" value="Y" {if $shipping.service_params.sunday_shipping == 'Y'} checked="checked" {/if} {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>

<div class="control-group">
    <label for="elm_shipping_monday_rule" class="control-label">{__("calendar_delivery.monday_rule")}:</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][monday_rule]" value="N">
        <input type="checkbox" name="shipping_data[service_params][monday_rule]" id="elm_shipping_monday_rule" value="Y" {if $shipping.service_params.monday_rule == 'Y'} checked="checked" {/if} {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>

<div class="control-group">
    <label for="elm_shipping_period_start" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="{__('period_start_error_message')}">{__("calendar_delivery.period_start")}:</label>
    <div class="controls">
        <input class="input-mini cm-trim" id="elm_shipping_period_start" size="5" maxlength="5" type="text" name="shipping_data[service_params][period_start]" value="{$shipping.service_params.period_start}" placeholder="00:00" {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>

<div class="control-group">
    <label for="elm_shipping_period_finish" class="control-label cm-regexp" data-ca-regexp="^(([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)*$" data-ca-message="{__('period_finish_error_message')}">{__("calendar_delivery.period_finish")}:</label>
    <div class="controls">
        <input class="input-mini cm-trim" id="elm_shipping_period_finish" size="5" maxlength="5" type="text" name="shipping_data[service_params][period_finish]" value="{$shipping.service_params.period_finish}" placeholder="00:00" {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>

<div class="control-group">
    <label for="elm_shipping_period_step" class="control-label">{__("calendar_delivery.period_step")}:</label>
    <div class="controls">
        <input class="input-micro cm-trim" id="elm_shipping_period_step" size="2" maxlength="2" type="text" name="shipping_data[service_params][period_step]" value="{$shipping.service_params.period_step}" placeholder="2" {if $disabled}disabled="_disabled"{/if}/>
    </div>
</div>
