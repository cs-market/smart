{hook name="menu:profile"}
    {* REMOVE FEEDBACK *}
    {if !$show_languages_in_header_menu}
        {$smarty.capture.languages_menu nofilter}
    {/if}
    {if !$show_currencies_in_header_menu}
        {$smarty.capture.currencies_menu nofilter}
    {/if}
    <li><a href="{"profiles.update?user_id=`$auth.user_id`"|fn_url}">{__("edit_profile")}</a></li>
    {if "MULTIVENDOR"|fn_allowed_for && !$runtime.simple_ultimate && $auth.user_type == "UserTypes::ADMIN"|enum && fn_check_view_permissions("companies.get_companies_list", "GET") && fn_check_view_permissions("profiles.login_as_vendor", "POST")}
        <li id="company_picker_dropdown_menu"
            class="js-company-switcher"
            data-ca-switcher-param-name="company_id"
            data-ca-switcher-data-name="company_id">
            {include file="views/companies/components/picker/picker.tpl"
                input_name=$companies_picker_name
                item_ids=[$runtime.company_data.company_id]
                type="list"
                show_advanced=false
                selection_title_pre=__("log_in_as_vendor")
                dropdown_parent_selector="#company_picker_dropdown_menu"
            }
        </li>
    {/if}
    {hook name="menu:profile_menu_extra_item"}
    {/hook}
    <li><a href="{"auth.logout"|fn_url}">{__("sign_out")}</a></li>
{/hook}
