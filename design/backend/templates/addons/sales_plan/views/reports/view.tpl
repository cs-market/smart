{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

{assign var="r_url" value=$config.current_url|escape:url}

<div class="items-container" >
    <input type="hidden" name="result_ids" value="manage_robots" />
    {if $report}
        <div class="table-responsive-wrapper" style="max-width: 960px; overflow-x: scroll;">
        <table class="table table-middle table-responsive" width="100%">
            <thead>
                <tr>
                    {foreach from=$report[0]|array_keys item="header"}
                        <th>{$header}</th>
                    {/foreach}
                </tr>
            </thead>
            <tbody>
                {foreach from=$report item="item"}
                <tr>
                    {foreach from=$item item="value"}
                    <td>
                        {$value}
                    </td>
                    {/foreach}
                </tr>
                {/foreach}
            </tbody>

        </table>
        </div>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--manage_robots--></div>

{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
    <h6>{__("filter")}</h6>
    <form name="thread_search_form" action="{""|fn_url}" method="get" class="{$form_meta}" id="sales_plans_form">
        <input type="hidden" name="is_search" value="Y">
        {foreach from=$search_schema item='item' key='key'}
            {$name = $item.name}
            {if $item.type == 'customer_picker'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}">{__($item.label)}</label>
                    <div class="break">
                        {include file="pickers/users/picker.tpl" display="checkbox" but_meta="btn" item_ids=$search.$name data_id="0" input_name=$name}
                    </div>
                </div>
            {elseif $item.type == 'product_picker'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}">{__($item.label)}</label>
                    <div class="break">
                        {include file="pickers/products/picker.tpl" display="checkbox" but_meta="btn" item_ids=$search.$name data_id="0" input_name=$name type="links"}
                    </div>
                </div>
            {elseif $item.type == 'category_picker'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}">{__($item.label)}</label>
                    <div class="break">
                        {include file="pickers/categories/picker.tpl" display="checkbox" but_meta="btn" item_ids=$search.$name data_id="0" input_name=$name}
                    </div>
                </div>
            {elseif $item.type == 'usergroup_selectbox'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}">{__($item.label)}</label>
                    <div class="break">
                        <select name="{$name}" id="elm_{$key}">
                            <option value="">--</option>
                            {assign var="usergroups" value="C"|fn_get_usergroups}
                            {foreach from=$usergroups item="usergroup" key="usergroup_id"}
                                <option value="{$usergroup_id}" {if $search.$name == $usergroup_id} selected="selected" {/if}>{$usergroup.usergroup}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {elseif $item.type == 'company_field'}
                {if !$runtime.company_id}
                    {include file="views/companies/components/company_field.tpl"
                        name=$name
                        id="elm_company_id"
                        zero_company_id_name_lang_var="none"
                        selected=$search.$name
                        disable_company_picker=$disable_company_picker
                    }
                {/if}
            {elseif $item.type == 'period_selector'}
                {include file="common/period_selector.tpl" period=$search.period display="form"}
            {elseif $item.type == 'checkbox'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}" class="pull-left">{__($item.label)}</label>
                    <div class="break pull-right">
                        <input type="hidden" name="{$name}" value="N">
                        <input id="elm_{$key}" type="checkbox" name="{$name}" {if ($search.$name == "Y") || (!$search.$name && $item.selected)} checked="checked" {/if} value="Y">
                    </div>
                </div>
            {elseif $item.type == 'select'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}">{__($item.label)}</label>
                    <div class="break">
                        <select name="{$name}" id="elm_{$key}">
                            {foreach from=$item.variants item="variant"}
                                {if __($variant)|strpos:'_' === 0}
                                    {$var = $variant}
                                {else}
                                    {$var = __($variant)}
                                {/if}
                                <option value="{$variant}" {if $search.$name == $variant}selected="selected"{/if}>{$var}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {elseif $item.type == 'input'}
                <div class="sidebar-field {$item.class}">
                    <label for="elm_{$key}">{__($item.label)}</label>
                    <div class="break">
                        <input type="text" name="{$name}" value="{$search.$name}">
                    </div>
                </div>
            {elseif $item.type == 'hidden'}
                <input type="hidden" name="{$name}" value="{$item.value}">
            {elseif $item.type == 'delimeter'}
                <div class="sidebar-field {$item.class}">
                    <div class="clearfix"></div>
                </div>
            {elseif $item.type == 'button'}
                <div class="sidebar-field {$item.class}">
                {include file="buttons/button.tpl" but_name=$item.but_name but_role=$item.but_role but_target_form="sales_plans_form" but_text=$item.but_text but_meta=$item.but_meta}
                </div>
            {/if}
            {hook name="sales_plan:manage_items"}
            {/hook}
        {/foreach}
    </form></div>
    {*<script type="text/javascript">
        $('#elm_only_zero').click(function () {
            if ($(this).prop( "checked" )) {
                select = $("select[id$='period_selects']", $(this).closest('form'));
                $("option[value='D']", select).prop('selected', true).change();
            }
        });
    </script>*}
{/capture}


{include file="common/mainbox.tpl" title=__($search.type) content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
