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
<div class="control-group hidden">
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
<div class="control-group">
    {include file="common/select_vendor.tpl"}
</div>

</div><div class="group span6 form-horizontal">
<div class="control-group">
    <label class="control-label" for="elm_user_orders">{__("orders")}</label>
    <div class="controls">
        <select name="user_orders" id="elm_user_orders" onchange="fn_change_period_avalability(!Tygh.$(this).val(), 'orders_period_');">
            <option value="">--</option>
            <option value="with" {if $search.user_orders == 'with'}selected="_selected"{/if}>{__("with_orders")}</option>
            <option value="without" {if $search.user_orders == 'without'}selected="_selected"{/if}>{__("without_orders")}</option>
        </select>
    </div>
</div>

{include
    file="common/period_selector.tpl"
    period=$search.orders_period
    prefix="orders_period_"
    display="form"
}

<script type="text/javascript">
    $(document).ready(function() {
        flag = !$('#elm_user_orders').val();
        fn_change_period_avalability(flag, 'orders_period_');
    });

    function fn_change_period_avalability(flag, prefix) {
        $('[name="' + prefix + 'time_from"]').prop('disabled', flag);
        $('[name="' + prefix + 'time_to"]').prop('disabled', flag);
        $('[name="' + prefix + 'period"]').prop('disabled', flag);
    }
</script>
