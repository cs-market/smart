<div class="control-group">
    <label for="notify_vendor" class="checkbox">{__("is_send_sms")}
    	<input type="checkbox" class="" {if $notify_vendor_status == true} checked="checked" {/if} name="notify_vendor_sms" id="notify_vendor_sms" value="Y" />
    </label>
</div>
<div class="control-group">
	<label for="custom_sms_send">{__("custom_sms")}</label>
	<textarea class="span12 user-success" name="custom_sms_content" id="custom_sms_send" cols="40" rows="5"></textarea>
	{*<a class="btn btn-primary cm-submit" data-ca-dispatch="dispatch[order_management.send_custom_sms]">{__("send_custom_sms")}</a>*}
	<input class="btn btn-primary" type="submit" name="dispatch[order_management.send_custom_sms]" value="{__("send_custom_sms")}" />
</div>
