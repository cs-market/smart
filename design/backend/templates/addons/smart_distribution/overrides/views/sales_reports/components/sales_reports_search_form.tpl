<div class="sidebar-row">
<form action="{""|fn_url}" method="post" name="report_form_{$report.report_id}">
<h6>{__("search")}</h6>
    {capture name="simple_search"}
        <div class="sidebar-field">
            <label for="elm_categories">{__("categories")}</label>
            <div class="break">
                {include file="pickers/categories/picker.tpl" display="checkbox" multiple='true' but_meta="btn" extra_url=$extra_url item_ids=$dynamic_conditions.category data_id="0" input_name="dynamic_conditions[category]" }

            </div>
        </div>
        <div class="sidebar-field">
            <label for="elm_customer">{__("customer")}</label>
            <div class="break">
                {include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" extra_url=$extra_url item_ids=$dynamic_conditions.user data_id="0" input_name="dynamic_conditions[user]"}
            </div>
        </div>
        {hook name="sales_reports::search_form"}
        {/hook}
        {include file="views/companies/components/company_field.tpl"
            name="dynamic_conditions[company_id]"
            id="dynamic_conditions_company_id"
            selected=$dynamic_conditions.company_id
            zero_company_id_name_lang_var='none'
        }
        <div class="sidebar-field">
            <label for="elm_usergroup">{__("usergroup")}</label>
            <div class="break">
                <select name="dynamic_conditions[usergroup_id]" id="elm_usergroup">
                    <option value="">--</option>
                    {assign var="usergroups" value="C"|fn_get_usergroups}
                    {foreach from=$usergroups item="usergroup" key="usergroup_id"}
                        <option value="{$usergroup_id}" {if $dynamic_conditions.usergroup_id == $usergroup_id} selected="selected" {/if}>{$usergroup.usergroup}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <input type="hidden" name="report_id" value="{$report.report_id}">
        <input type="hidden" name="selected_section" value="">
        {include file="common/period_selector.tpl" period=$period display="form"}

        <div class="sidebar-field">
            <label for="elm_group_by">{__("sales_plan.group_by")}</label>
            <div class="break">
                <select name="dynamic_conditions[interval_id]" id="elm_group_by">
                    <option value="3" {if $dynamic_conditions.interval_id == '3'}selected="selected"{/if}>{__('day')}</option>
                    <option value="5" {if $dynamic_conditions.interval_id == '5'}selected="selected"{/if}>{__('week')}</option>
                    <option value="7" {if $dynamic_conditions.interval_id == '7'}selected="selected"{/if}>{__('month')}</option>
                </select>
            </div>
        </div>

        <div class="sidebar-field">
            <label for="elm_group_by">{__("value_to_display")}</label>
            <div class="break">
                <select name="dynamic_conditions[display]" id="elm_group_by">
                    <option value="product_number" {if $dynamic_conditions.display == 'product_number'}selected="selected"{/if}>{__('reports_parameter_14')}</option>
                    <option value="product_cost" {if $dynamic_conditions.display == 'product_cost'}selected="selected"{/if}>{__('reports_parameter_13')}</option>
                </select>
            </div>
        </div>
    {/capture}
    {include file="common/advanced_search.tpl" no_adv_link=true simple_search=$smarty.capture.simple_search not_saved=true dispatch="sales_reports.set_report_view"}
</form>
</div>
