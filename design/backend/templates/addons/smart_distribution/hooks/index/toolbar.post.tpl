<div class="pull-left mobile-visible">
{if $auth.company_id}
    <li class="dropdown">
        <a href="{"companies.update?company_id=`$runtime.company_id`"|fn_url}">{__("vendor")}: {$runtime.company_data.company}</a>
    </li>
{elseif fn_check_view_permissions("companies.update", "GET")}
    {capture name="extra_content"}
        <li class="divider"></li>
        <li><a href="{"companies.manage?switch_company_id=0"|fn_url}">{__("manage_vendors")}...</a></li>
    {/capture}

    {include file="common/ajax_select_object.tpl" data_url="companies.get_companies_list?show_all=Y&action=href" text=$runtime.company_data.company id="top_mobile_company_id" type="ddown" extra_content=$smarty.capture.extra_content}
{/if}
</div>
