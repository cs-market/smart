{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}
<form name="category_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $put_request_vars}
    {array_to_fields data=$smarty.request skip=["callback"] escape=["data_id"]}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field">
    <label for="elm_name">{__("Name")}</label>
    <div class="break">
        <input type="text" name="search_query" id="elm_name" value="{$search.search_query}" />
    </div>
</div>
{include file="views/companies/components/company_field.tpl"
    name="company_id"
    id="elm_company_id"
    zero_company_id_name_lang_var="none"
    selected=$search.company_id
    disable_company_picker=$disable_company_picker
}

{include file="common/period_selector.tpl" period=$search.period display="form"}
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search="" dispatch=$dispatch view_type="categories" in_popup=1}

</form>

{if $in_popup}
</div></div>
{else}
</div><hr>
{/if}