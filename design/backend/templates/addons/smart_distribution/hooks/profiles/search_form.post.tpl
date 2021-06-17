{$managers = ""|fn_smart_distribution_get_managers}
<div class="control-group">
    <label class="control-label" for="elm_managers">{__("manager")}</label>
    <div class="controls">
    <select name="managers" id="elm_managers">
    <option value="">--</option>
        {foreach from=$managers item="manager" key="user_id"}
            <option value="{$user_id}">{$manager.name}</option>
        {/foreach}
    </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="elm_managers">{__("user_type")}</label>
    <div class="controls">
        <select id="user_type" name="user_type">
            <option value="">{__("all")}</option>
            <option value="C" {if $search.user_type == "C"}selected="selected"{/if}>{__("customer")}</option>
            <option value="V" {if $search.user_type == "V"}selected="selected"{/if}>{__("vendor_administrator")}</option>
            <option value="A" {if $search.user_type == "A"}selected="selected"{/if}>{__("administrator")}</option>
        </select>
    </div>
</div>

</div><div class="group span6 form-horizontal">
<div class="control-group">
    <label class="control-label" for="elm_wo_orders">{__("wo_orders")}</label>
    <div class="controls">
        <input type="hidden" name="wo_orders" value="N">
        <input type="checkbox" id="elm_wo_orders" name="wo_orders" value="Y" {if $search.wo_orders == 'Y'}checked="checked"{/if} onclick="fn_change_period_avalability(!this.checked);">
    </div>
</div>
{include
    file="common/period_selector.tpl"
    period=$search.without_order_period
    prefix="without_order_"
    display="form"
}

<script type="text/javascript">
    $(document).ready(function() {
        flag = !$('#elm_wo_orders').is(':checked');
        fn_change_period_avalability(flag);
    });

    function fn_change_period_avalability(flag) {
        $('[name="without_order_time_from"]').prop('disabled', flag);
        $('[name="without_order_time_to"]').prop('disabled', flag);
        $('[name="without_order_period"]').prop('disabled', flag);
    }
</script>