{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="shops_form" id="shops_form">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"icon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"icon-dummy\"></i>"}

{assign var="return_url" value=$config.current_url|escape:"url"}

{if $shops}
<div class="table-responsive-wrapper">
    <table width="100%" class="table table-middle table-responsive">
    <thead>
    <tr>
        <th width="1%" class="left mobile-hide">
            {include file="common/check_items.tpl"}</th>
        <th width="6%"><a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=shop&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("name")}{if $search.sort_by == "shop"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=storefront&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("storefront")}{if $search.sort_by == "storefront"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("registered")}{if $search.sort_by == "date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        {hook name="shops:list_extra_th"}{/hook}
        <th width="10%" class="nowrap">&nbsp;</th>
    </tr>
    </thead>
    {foreach from=$shops item=shop}
    <tr class="cm-row-status-{$shop.status|lower}" data-ct-shop-id="{$shop.shop_id}">
        <td class="left mobile-hide">
            <input type="checkbox" name="shop_ids[]" value="{$shop.shop_id}" class="cm-item" />
        </td>
        <td class="row-status" data-th="{__("id")}"><a href="{"shops.update?shop_id=`$shop.shop_id`"|fn_url}">&nbsp;<span>{$shop.shop_id}</span>&nbsp;</a></td>
        <td class="row-status" data-th="{__("name")}"><a href="{"shops.update?shop_id=`$shop.shop_id`"|fn_url}">{$shop.shop}</a></td>

            {$storefront_href = "http://`$shop.storefront`"}
            <td data-th="{__("storefront")}" id="storefront_url_{$shop.shop_id}"><a href="{$storefront_href}">{$shop.storefront|puny_decode}</a><!--storefront_url_{$shop.shop_id}--></td>

        <td class="row-status" data-th="{__("registered")}">{$shop.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
        {hook name="shops:list_extra_td"}{/hook}
        <td class="nowrap" data-th="{__("tools")}">
            {capture name="tools_items"}
            {hook name="shops:list_extra_links"}
                {if !$runtime.shop_id && fn_check_view_permissions("shops.update", "POST")}
                    <li>{btn type="list" href="shops.update?shop_id=`$shop.shop_id`" text=__("edit")}</li>
                    <li class="divider"></li>
                    {if $runtime.simple_ultimate}
                        <li class="disabled"><a>{__("delete")}</a></li>
                    {else}
                        <li>{btn type="list" class="cm-confirm" href="shops.delete?shop_id=`$shop.shop_id`&redirect_url=`$return_current_url`" text=__("delete") method="POST"}</li>
                    {/if}
                {/if}
            {/hook}
            {/capture}
            <div class="hidden-tools">
                {dropdown content=$smarty.capture.tools_items}
            </div>
        </td>
    </tr>
    {/foreach}
    </table>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}


{include file="common/pagination.tpl"}
</form>
{/capture}
{capture name="buttons"}
    {capture name="tools_items"}
        {hook name="shops:manage_tools_list"}
            {if $shops && !$runtime.shop_id && !"ULTIMATE"|fn_allowed_for}
                <li>{btn type="list" text=__("activate_selected") dispatch="dispatch[shops.export_range]" form="shops_form" class="cm-process-items cm-dialog-opener"  data=["data-ca-target-id" => "content_activate_selected"]}</li>                    
                <li>{btn type="list" text=__("disable_selected") dispatch="dispatch[shops.export_range]" form="shops_form" class="cm-process-items cm-dialog-opener"  data=["data-ca-target-id" => "content_disable_selected"]}</li>                    
            {/if}
            {if !$runtime.shop_id && fn_check_view_permissions("shops.update", "POST")}
                <li>{btn type="delete_selected" dispatch="dispatch[shops.m_delete]" form="shops_form"}</li>
            {/if}
            {if $shops && "MULTIshop"|fn_allowed_for}
                <li>{btn type="list" text=__("export_selected") dispatch="dispatch[shops.export_range]" form="shops_form"}</li>
            {/if}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_items class="mobile-hide"}
{/capture}

{capture name="adv_buttons"}
    {if $is_shops_limit_reached}
        {$promo_popup_title = __("ultimate_or_storefront_license_required", ["[product]" => $smarty.const.PRODUCT_NAME])}

        {include file="common/tools.tpl" tool_override_meta="btn cm-dialog-opener cm-dialog-auto-height" tool_href="functionality_restrictions.ultimate_or_storefront_license_required" prefix="top" hide_tools=true title=__("add_shop") icon="icon-plus" meta_data="data-ca-dialog-title=\"$promo_popup_title\""}
    {else}
        {include file="common/tools.tpl" tool_href="shops.add" prefix="top" hide_tools=true title=__("add_shop") icon="icon-plus"}
    {/if}
{/capture}

{capture name="sidebar"}
    {hook name="shops:manage_sidebar"}
    {include file="common/saved_search.tpl" dispatch="shops.manage" view_type="shops"}
    {include file="addons/multishop/views/shops/components/shops_search_form.tpl" dispatch="shops.manage"}
    {/hook}
{/capture}

{include file="common/mainbox.tpl" title=__("shops") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}
