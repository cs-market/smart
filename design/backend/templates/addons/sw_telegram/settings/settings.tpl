<div id="storefront_settings">

<fieldset>
    <div class="form-field control-group">
        <label class="control-label">{__("sw_telegram_get_id")}:</label>
        <div  class="controls">
            {include file="buttons/button.tpl" but_role="submit" but_meta="btn-primary cm-skip-validation" data-ca-target-id="storefront_settings" but_name="dispatch[sw_telegram.get_id_chat]" but_text=__("sw_telegram_get_id_chat")}
        </div>
    </div>
     <div class="form-field control-group">
        <div  class="controls">
            <a href="{"system_tg.webhook&set=1"|fn_url:'C'}" target="blank" class="btn btn-primary"> {__("sw_telegram.reg_webhook")}</a>
            <p class="muted description">{__("sw_telegram.webhook_text")}</p>
        </div>
    </div>
</fieldset>

<!--storefront_settings--></div>
