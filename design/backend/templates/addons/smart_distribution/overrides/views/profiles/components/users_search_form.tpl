{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}
<form name="user_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $search.user_type}
<input type="hidden" name="user_type" value="{$search.user_type}" />
{/if}

{if $put_request_vars}
    {array_to_fields data=$smarty.request skip=["callback"] escape=["data_id"]}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field" style="min-width: 180px;">
    <label for="elm_name">{__("person_name")}</label>
    <div class="break">
        <input type="text" name="name" id="elm_name" value="{$search.name}" />
    </div>
</div>
{if $auth.user_type == "UserTypes::ADMIN"|enum && $search.user_type == "UserTypes::VENDOR"|enum}
    <div class="sidebar-field" style="min-width: 180px;">
    {include file="common/select_vendor.tpl"}
    </div>
{else}
    <div class="sidebar-field" style="min-width: 180px;">
        <label for="elm_phone">{__("phone")}</label>
        <div class="break">
            <input type="text" name="phone" id="elm_phone" value="{$search.phone}" />
        </div>
    </div>
{/if}
<div class="sidebar-field" style="min-width: 180px;">
    <label for="elm_email">{__("email")}</label>
    <div class="break">
        <input type="text" name="email" id="elm_email" value="{$search.email}" />
    </div>
</div>
{/capture}

{capture name="advanced_search"}
    <div class="row-fluid">
        <div class="group span6 form-horizontal">
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                <div class="control-group">
                    <label class="control-label" for="elm_usergroup_id">{__("usergroup")}</label>
                    <div class="controls">
                    <select name="usergroup_id" id="elm_usergroup_id">
                        <option value="{$smarty.const.ALL_USERGROUPS}"> -- </option>
                        <option value="0" {if $search.usergroup_id === "0"}selected="selected"{/if}>{__("not_a_member")}</option>
                        {foreach from=$usergroups item=usergroup}
                        <option value="{$usergroup.usergroup_id}" {if $search.usergroup_id == $usergroup.usergroup_id}selected="selected"{/if}>{$usergroup.usergroup}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>
            {/if}
            {*<div class="control-group">
                <label class="control-label" for="elm_tax_exempt">{__("tax_exempt")}</label>
                <div class="controls">
                <select name="tax_exempt" id="elm_tax_exempt">
                    <option value="">--</option>
                    <option value="{"YesNo::YES"|enum}" {if $search.tax_exempt == "YesNo::YES"|enum}selected="selected"{/if}>{__("yes")}</option>
                    <option value="{"YesNo::NO"|enum}" {if $search.tax_exempt == "YesNO::NO"|enum}selected="selected"{/if}>{__("no")}</option>
                </select>
                </div>
            </div>*}

            {hook name="profiles:search_form"}{/hook}
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
    </div>
    <div class="group span6 form-horizontal">
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

        <div class="control-group">
            {include
                file="common/period_selector.tpl"
                period=$search.orders_period
                prefix="orders_period_"
                display="form"
            }
        </div>

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

        {if $auth.user_type == "UserTypes::ADMIN"|enum && $search.user_type == "UserTypes::VENDOR"|enum}
        <div class="control-group">
            <label class="control-label" for="elm_phone">{__("phone")}</label>
            <div class="controls">
                <input type="text" name="phone" id="elm_phone" value="{$search.phone}" />
            </div>
        </div>
        {/if}
        {* <div class="control-group">
            <label class="control-label" for="elm_city">{__("city")}</label>
            <div class="controls">
                <input type="text" name="city" id="elm_city" value="{$search.city}" />
            </div>
        </div>
        <div class="control-group">
            <label for="srch_country" class="control-label">{__("country")}</label>
            <div class="controls">
            <select id="srch_country" name="country" class="cm-country cm-location-search">
                <option value="">- {__("select_country")} -</option>
                {foreach from=$countries item="country" key="code"}
                <option value="{$code}" {if $search.country == $code}selected="selected"{/if}>{$country}</option>
                {/foreach}
            </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_company">{__("company")}</label>
            <div class="controls">
                <input type="text" name="company" id="elm_company" value="{$search.company}" />
            </div>
        </div>
        <div class="control-group">
            <label for="srch_state" class="control-label">{__("state")}</label>
            <div class="controls">
                <select id="srch_state" class="cm-state cm-location-search hidden" name="state_code">
                    <option value="">- {__("select_state")} -</option>
                </select>
                <input class="cm-state cm-location-search" type="text" id="srch_state_d" name="state" maxlength="64" value="{$search.state}" disabled="disabled" />
            </div>
        </div> *}
        <div class="control-group">
            <label class="control-label" for="elm_address">{__("address")}</label>
            <div class="controls">
                <input type="text" name="address" id="elm_address" value="{$search.address}" />
            </div>
        </div>
        {* <div class="control-group">
            <label class="control-label" for="elm_zipcode">{__("zip_postal_code")}</label>
            <div class="controls">
                <input type="text" name="zipcode" id="elm_zipcode" value="{$search.zipcode}" />
            </div>
        </div> *}

    </div>
</div>

<div class="group">
    <div class="control-group">
        <label class="control-label">{__("products")}</label>
        <div class="controls">
            <label class="radio inline" for="elm_ordered_type_y">
                <input type="radio" name="ordered_type" class="" id="elm_ordered_type_y" {if $search.ordered_type != 'NIN'}checked="checked"{/if} value="IN">{__("in_order")}
            </label>
            <label class="radio inline" for="elm_ordered_type_n">
                <input type="radio" name="ordered_type" class="" id="elm_ordered_type_n" {if $search.ordered_type == 'NIN'}checked="checked"{/if} value="NIN">{__("nin_order")}
            </label>
        </div>
        <div class="controls">
            {include file="common/products_to_search.tpl" placement="right"}
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("category_products")}</label>
        <div class="controls">
            {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="category_ids" item_ids=$search.category_ids hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
        </div>
    </div>
    
    <div class="control-group">
        <div class="controls">
            {include
                file="common/period_selector.tpl"
                period=$search.ordered_products_period
                prefix="ordered_products_"
                display="form"
            }
        </div>
    </div>
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="users" in_popup=$in_popup}

</form>

{if $in_popup}
</div></div>
{else}
</div><hr>
{/if}
