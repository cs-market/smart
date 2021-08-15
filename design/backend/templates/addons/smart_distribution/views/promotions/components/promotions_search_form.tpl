{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}

<form name="promotion_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

    {if $smarty.request.redirect_url}
        <input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
    {/if}

    {if $selected_section != ""}
        <input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
    {/if}

    {if $put_request_vars}
        {array_to_fields data=$smarty.request skip=["callback"]}
    {/if}

    {$extra nofilter}

    {capture name="simple_search"}
        <div class="sidebar-field">
            <label for="elm_name">{__("promotion")}</label>
            <div class="break">
                <input type="text" name="name" id="elm_name" value="{$search.name}" />
            </div>
        </div>
        {if !$runtime.company_id}
            {include file="views/companies/components/company_field.tpl"
                name="company_id"
                id="elm_company_id"
                zero_company_id_name_lang_var="none"
                selected=$search.company_id
                disable_company_picker=$disable_company_picker
            }
        {/if}
        <div class="sidebar-field">
            <label for="elm_type">{__("zone")}</label>
            <div class="controls">
                <select name="zone" id="elm_type">
                    {hook name="promotions:search_form_promotion_type"}
                    <option value="">{__("any")}</option>
                    <option {if $search.zone == "catalog"}selected="selected"{/if} value="catalog">{__("catalog")}</option>
                    <option {if $search.zone == "cart"}selected="selected"{/if} value="cart">{__("cart")}</option>
                    {/hook}
                </select>
            </div>
        </div>

        <div class="sidebar-field">
            <label for="elm_type">{__("status")}</label>
            {assign var="items_status" value=""|fn_get_default_statuses:true}
            <div class="controls">
                <select name="status" id="elm_type">
                    <option value="">{__("all")}</option>
                    {foreach from=$items_status key=key item=status}
                        <option value="{$key}" {if $search.status == $key}selected="selected"{/if}>{$status}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    {/capture}

    {include file="common/advanced_search.tpl" no_adv_link=true simple_search=$smarty.capture.simple_search dispatch=$dispatch view_type="promotions"}

</form>

{if $in_popup}
    </div></div>
{else}
    </div><hr>
{/if}
