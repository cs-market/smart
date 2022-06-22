{hook name="order_management:notify_checkboxes"}
    <div class="control-group">
        <label for="notify_user" class="checkbox">{__("notify_customer")}
        <input type="checkbox" class="" {if $notify_customer_status == true} checked="checked" {/if} name="notify_user" id="notify_user" value="Y" /></label>
    </div>
    {if $smarty.const.ACCOUNT_TYPE === "admin"}
        <div class="control-group">
            <label for="notify_department" class="checkbox">{__("notify_orders_department")}
            <input type="checkbox" class="" {if $notify_department_status == true} checked="checked" {/if} name="notify_department" id="notify_department" value="Y" /></label>
        </div>
        {if fn_allowed_for("MULTIVENDOR")}
        <div class="control-group">
            <label for="notify_vendor" class="checkbox">{__("notify_vendor")}
            <input type="checkbox" class="" {if $notify_vendor_status == true} checked="checked" {/if} name="notify_vendor" id="notify_vendor" value="Y" /></label>
        </div>
        {/if}
    {/if}
{/hook}
