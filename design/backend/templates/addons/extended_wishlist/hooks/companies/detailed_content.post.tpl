{if "MULTIVENDOR"|fn_allowed_for}
{include file="common/subheader.tpl" title=__("extended_wishlist.extended_wishlist")}

<div class="control-group">
    <label for="elm_company_add_order_to_wl" class="control-label">{__("extended_wishlist.add_order_to_wl")}:</label>
    <div class="controls">
        <input type="hidden" name="company_data[add_order_to_wl]" value="N">
        <input type="checkbox" name="company_data[add_order_to_wl]" id="elm_company_add_order_to_wl" value="Y" {if $company_data.add_order_to_wl == "YesNo::YES"|enum}checked="_checked"{/if}>
    </div>
</div>
{/if}
