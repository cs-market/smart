{capture name="phone_search"}
<div class="sidebar-field hidden">
    <label for="ecl_phone">{__("phone")}</label>
    <input type="text" name="phone" id="ecl_phone" value="{$search.phone}" size="30" />
</div>
<script type="text/javascript">
Tygh.$(document).ready(function() {
    if ($("input[name='phone']" ).length > 1) {
        $('#ecl_phone').parent().remove();
    } else {
        $('#ecl_phone').parent().show();
    }
});
</script>
{/capture}

{include file="views/orders/components/../components/orders_search_form.tpl" dispatch="orders.manage" extra=$smarty.capture.phone_search}