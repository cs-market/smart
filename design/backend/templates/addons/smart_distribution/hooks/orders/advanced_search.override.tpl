{capture name="simple_search"}

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}
{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{$extra nofilter}

<div class="sidebar-field" style="min-width: 180px;">
    <label for="cname">{__("customer")}</label>
    <input type="text" name="cname" id="cname" value="{$search.cname}"/>
</div>

<div class="sidebar-field" style="min-width: 180px;">
    <label for="email">{__("email")}</label>
    <input type="text" name="email" id="email" value="{$search.email}"/>
</div>

<div class="sidebar-field" style="min-width: 180px;">
    <label for="phone">{__("phone")}</label>
    <input class="cm-phone" type="text" name="phone" id="phone" value="{$search.phone}"/>
</div>

<div class="sidebar-field" style="min-width: 180px;">
    <label for="total_from">{__("total")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
    <input type="text" class="input-small" name="total_from" id="total_from" value="{$search.total_from}" size="3" /> - <input type="text" class="input-small" name="total_to" value="{$search.total_to}" size="3" />
</div>

<div class="sidebar-field" style="min-width: 180px;">
    <label for="get_totals">{__("get_totals")}</label>
    <input class="" type="hidden" name="get_totals" value="{"YesNo::NO"|enum}"/>
    <input class="" type="checkbox" name="get_totals" id="get_totals" value="{"YesNo::YES"|enum}" {if $search.get_totals == "YesNo::YES"|enum}checked="_checked"{/if}/>
</div>

{/capture}

{hook name="orders:advanced_search"}

<div class="group form-horizontal">
<div class="control-group">
    <label class="control-label">{__("period")}</label>
    <div class="controls">
        {include file="common/period_selector.tpl" period=$search.period form_name="orders_search_form"}
    </div>
</div>
</div>

<div class="group">
<div class="control-group">
    <label class="control-label">{__("order_status")}</label>
    <div class="controls checkbox-list">
        {include file="common/status.tpl" status=$search.status display="checkboxes" name="status" columns=5}
    </div>
</div>
</div>

<div class="row-fluid">
    <div class="group span6 form-horizontal">
    <div class="control-group">
        <label class="control-label" for="order_id">{__("order_id")}</label>
        <div class="controls">
            <input type="text" name="order_id" id="order_id" value="{$search.order_id}" size="10"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_company">{__("company")}</label>
        <div class="controls">
            <input type="text" name="company" id="elm_company" value="{$search.company}" size="10"/>
        </div>
    </div>
    </div>

    <div class="group span6 form-horizontal">
        {include file="common/select_vendor.tpl"}
    </div>
</div>
<div class="group">
<div class="control-group">
    <label class="control-label">{__("shipping")}</label>
    <div class="controls checkbox-list">
        {html_checkboxes name="shippings" options=$shippings selected=$search.shippings columns=4}
    </div>
</div>
</div>

<div class="group">
<div class="control-group">
    <label class="control-label">{__("payment_methods")}</label>
    <div class="controls checkbox-list">
        {html_checkboxes name="payments" options=$payments selected=$search.payments columns=4}
    </div>
</div>
</div>
<div class="group">
    <div class="control-group">
        <label class="control-label">{__("ordered_products")}</label>
        <div class="controls ">
            {include file="common/products_to_search.tpl" placement="right"}
        </div>
    </div>
</div>
{/hook}
