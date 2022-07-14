{if $shop_data.shop_id}
    {assign var="id" value=$shop_data.shop_id}
{else}
    {assign var="id" value=0}
{/if}


{capture name="mainbox"}

{capture name="tabsbox"}
{** /Item menu section **}

<form class="form-horizontal form-edit {$form_class} {if !fn_check_view_permissions("shops.update", "POST")}cm-hide-inputs{/if} " action="{""|fn_url}" method="post" id="shop_update_form" enctype="multipart/form-data"> {* shop update form *}
{* class=""*}
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
<input type="hidden" name="shop_id" value="{$id}" />

{** General info section **}
<div id="content_detailed" class="hidden"> {* content detailed *}
<fieldset>

{include file="common/subheader.tpl" title=__("information")}

{hook name="shops:general_information"}

    <div class="control-group">
        <label for="elm_shop_name" class="control-label cm-required">{__("shop_name")}:</label>
        <div class="controls">
            <input type="text" name="shop_data[shop]" id="elm_shop_name" size="32" value="{$shop_data.shop}" class="input-large" />
        </div>
    </div>

    {hook name="shops:storefronts"}
    <div class="control-group">
        <label for="elm_shop_storefront" class="control-label cm-required">{__("storefront_url")}:</label>
        <div class="controls">
        {if $runtime.shop_id}
            http://{$shop_data.storefront|puny_decode}
        {else}
            <input type="text" name="shop_data[storefront]" id="elm_shop_storefront" size="32" value="{$shop_data.storefront|puny_decode}" class="input-large" placeholder="http://" />
        {/if}
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_shop_secure_storefront">{__("secure_storefront_url")}:</label>
        <div class="controls">
        {if $runtime.shop_id}
            https://{$shop_data.secure_storefront|puny_decode}
        {else}
            <input type="text" name="shop_data[secure_storefront]" id="elm_shop_secure_storefront" size="32" value="{$shop_data.secure_storefront|puny_decode}" class="input-large" placeholder="https://" />
        {/if}
        </div>
    </div>
    {include file="views/companies/components/company_field.tpl"
        name="shop_data[company_id]"
        id="elm_company_id"
        zero_company_id_name_lang_var="none"
        selected=$shop_data.company_id
    }

    <div class="control-group">
        <label class="control-label">{__("usergroups")}:</label>
        <div class="controls">
            {include file="common/select_usergroups.tpl" id="ug_id" name="shop_data[usergroup_ids]" usergroups=["type"=>"C", "status"=>["A", "H"]]|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$shop_data.usergroup_ids input_extra="" list_mode=false}
        </div>
    </div>
    {/hook}
    {hook name="shops:storefronts_design"}

        {if $id}
        {*<div class="control-group">
            <label class="control-label" for="elm_shop_stores_status">{__("stores_status")}:</label>
            <div class="controls">
                {include file="views/shops/components/shop_status_switcher.tpl" shop=$shop_data}
                <p>{__("storefront_status_access_key_hint", ["[url]" => fn_url("settings.manage?switch_shop_id=`$id`&section_id=General&highlight=store_access_key")])}</p>
            </div>
        </div>*}

        {include file="common/subheader.tpl" title=__("design")}

        <div class="control-group">
            <label class="control-label">{__("store_theme")}:</label>
            <div class="controls">
                <p>{$theme_info.title}: {$current_style.name}</p>
                <a href="{"themes.manage?switch_shop_id=`$id`"|fn_url}">{__("goto_theme_configuration")}</a>
            </div>
        </div>
        {else}
            {* TODO: Make theme selector *}
            <input type="hidden" value="responsive" name="shop_data[theme_name]">
        {/if}
    {/hook}

    {if !$runtime.shop_id}
        {include file="common/select_status.tpl" input_name="shop_data[status]" id="shop_data" obj=$shop_data items_status="shops"|fn_get_predefined_statuses:$shop_data.status}
    {else}
        <div class="control-group">
            <label class="control-label">{__("status")}:</label>
            <div class="controls">
                <label class="radio"><input type="radio" checked="checked" />{if $shop_data.status == "A"}{__("active")}{elseif $shop_data.status == "P"}{__("pending")}{elseif $shop_data.status == "N"}{__("new")}{elseif $shop_data.status == "D"}{__("disabled")}{/if}</label>
            </div>
        </div>
    {/if}
{/hook}

</fieldset>
</div> {* /content detailed *}
{** /General info section **}

{if "MULTIshop"|fn_allowed_for}
    {** shop logos section **}
    <div id="content_logos" class="hidden">
        {hook name="shops:logos"}
        {include file="views/shops/components/logos_list.tpl" logos=$shop_data.logos shop_id=$id}
        {/hook}
    </div>
    {** /shop logos section **}
{/if}


{if "ULTIMATE"|fn_allowed_for}
{** shop regions settings section **}
<div id="content_regions" class="hidden">
    <fieldset>
        <div class="control-group">
            <div class="controls">
            <input type="hidden" name="shop_data[redirect_customer]" value="N" checked="checked"/>
            <label class="checkbox"><input type="checkbox" name="shop_data[redirect_customer]" id="sw_shop_redirect" {if $shop_data.redirect_customer == "Y"}checked="checked"{/if} value="Y" class="cm-switch-availability cm-switch-inverse" />{__("redirect_customer_from_storefront")}</label>
            </div>
        </div>

        <div class="control-group" id="shop_redirect">
            <label class="control-label" for="elm_shop_entry_page">{__("entry_page")}</label>
            <div class="controls">
            <select name="shop_data[entry_page]" id="elm_shop_entry_page" {if $shop_data.redirect_customer == "Y"}disabled="disabled"{/if}>
                <option value="none" {if $shop_data.entry_page == "none"}selected="selected"{/if}>{__("none")}</option>
                <option value="index" {if $shop_data.entry_page == "index"}selected="selected"{/if}>{__("index")}</option>
                <option value="all_pages" {if $shop_data.entry_page == "all_pages"}selected="selected"{/if}>{__("all_pages")}</option>
            </select>
            </div>
        </div>

        {include file="common/double_selectboxes.tpl"
            title=__("countries")
            first_name="shop_data[countries_list]"
            first_data=$shop_data.countries_list
            second_name="all_countries"
            second_data=$countries_list}
    </fieldset>
</div>
{** /shop regions settings section **}

{/if}

<div id="content_addons" class="hidden">
    {hook name="shops:detailed_content"}{/hook}
</div>

{hook name="shops:tabs_content"}{/hook}

</form> {* /product update form *}

{hook name="shops:tabs_extra"}{/hook}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name="shops" active_tab=$smarty.request.selected_section track=true}

{/capture}


{** Form submit section **}
{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[shops.update]" but_target_form="shop_update_form"}
{/capture}
{** /Form submit section **}

{if $id}
    {include file="common/mainbox.tpl"
        title_start=__("editing_shop")
        title_end=$shop_data.shop
        content=$smarty.capture.mainbox
        buttons=$smarty.capture.buttons
        sidebar=$smarty.capture.sidebar}
{else}
    {include file="common/mainbox.tpl" title=__("new_shop") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons}
{/if}
