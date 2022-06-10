<input type="hidden" name="shipping_data[service_params][configured]" value="Y" />
<input type="hidden" name="shipping_data[service_params][limit_weekday]" value="C">

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
