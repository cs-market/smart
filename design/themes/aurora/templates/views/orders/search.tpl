<form action="{""|fn_url}" class="ty-orders-search-options" name="orders_search_form" method="get">

<div class="clearfix ty-orders-search__params">
    {if $auth.user_id}
    <div class="span4 ty-control-group">
        <label class="ty-control-group__title">{__("order_id")}</label>
        <input type="text" name="order_id" value="{$search.order_id}" size="10" class="ty-search-form__input" />
    </div>
    {/if}

    <div class="span4 ty-control-group">
        <label class="ty-control-group__title">{__("total")}&nbsp;({$currencies.$secondary_currency.symbol nofilter})</label>
        <input type="text" name="total_sec_from" value="{$search.total_sec_from}" size="3" class="ty-control-group__price" />&nbsp;&#8211;&nbsp;<input type="text" name="total_sec_to" value="{$search.total_sec_to}" size="3" class="ty-control-group__price" />
    </div>

    {include file="common/period_selector.tpl" period=$search.period form_name="orders_search_form"}


    <div class="ty-buttons-container">
        {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("search") but_name="dispatch[orders.search]"}
    </div>
</div>

{capture name="section"}
<div class="ty-control-group">
    <label class="ty-control-group__title">{__("order_status")}</label>
    {include file="common/status.tpl" status=$search.status display="checkboxes" name="status" checkboxes_meta="ty-orders-search__options-status"}
</div>

{/capture}

{include file="common/section.tpl" section_title=__("search_options") section_content=$smarty.capture.section class="ty-search-form" collapse=true}
</form>


{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{if $search.sort_order == "asc"}
{assign var="sort_sign" value="<i class=\"ty-icon-down-dir\"></i>"}
{else}
{assign var="sort_sign" value="<i class=\"ty-icon-up-dir\"></i>"}
{/if}
{if !$config.tweaks.disable_dhtml}
    {assign var="ajax_class" value="cm-ajax"}

{/if}

{include file="common/pagination.tpl"}

<table class="ty-table ty-orders-search">
    <thead>
        <tr>
            <th><a class="{$ajax_class}" href="{"`$c_url`&sort_by=order_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("id")}</a>{if $search.sort_by == "order_id"}{$sort_sign nofilter}{/if}</th>
            <th><a class="{$ajax_class}" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}</a>{if $search.sort_by == "status"}{$sort_sign nofilter}{/if}</th>
            <th><a class="{$ajax_class}" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("customer")}</a>{if $search.sort_by == "customer"}{$sort_sign nofilter}{/if}</th>
            <th><a class="{$ajax_class}" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("order_date")}</a>{if $search.sort_by == "date"}{$sort_sign nofilter}{/if}</th>

            {hook name="orders:manage_header"}{/hook}

            <th><a class="{$ajax_class}" href="{"`$c_url`&sort_by=total&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("total")}</a>{if $search.sort_by == "total"}{$sort_sign nofilter}{/if}</th>
            <th class="ty-orders-search__header ty-orders-search__header--actions">{__("actions")}</th>
        </tr>
    </thead>
    {foreach from=$orders item="o"}
        <tr>
            <td class="ty-orders-search__item"><a href="{"orders.details?order_id=`$o.order_id`"|fn_url}"><strong>#{$o.order_id}</strong></a></td>
            <td class="ty-orders-search__item">{include file="common/status.tpl" status=$o.status display="view"}</td>
            <td class="ty-orders-search__item">
                <ul class="ty-orders-search__user-info">
                    <li class="ty-orders-search__user-name">{$o.firstname} {$o.lastname}</li>
                    <li  class="ty-orders-search__user-mail"><a href="mailto:{$o.email|escape:url}">{$o.email}</a></li>
                </ul>
            </td>
            <td class="ty-orders-search__item"><a href="{"orders.details?order_id=`$o.order_id`"|fn_url}">{$o.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</a></td>

            {hook name="orders:manage_data"}{/hook}

            <td class="ty-orders-search__item">{include file="common/price.tpl" value=$o.total}</td>
            <td class="ty-orders-search__item ty-orders-search__item--actions">
                {include file="buttons/button.tpl" 
                        but_meta="cm-new-window ty-btn-icon" 
                        but_role="text" 
                        but_title=__("print_invoice") 
                        but_href="orders.print_invoice?order_id=`$o.order_id`" 
                        but_icon="ty-orders__actions-icon ty-icon-print"}

                {include file="buttons/button.tpl" 
                        but_meta="ty-btn-icon"
                        but_role="text" 
                        but_title=__("re_order")
                        but_href="orders.reorder?order_id=`$o.order_id`" 
                        but_icon="ty-orders__actions-icon ty-icon-cw"}

                {include file="buttons/button.tpl" 
                        but_meta="ty-btn-icon" 
                        but_role="text" 
                        but_title=__("search_products")
                        but_href="products.search?search_performed=Y&order_ids=`$o.order_id`"
                        but_icon="ty-orders__actions-icon ty-icon-search"}
            </td>
        </tr>
    {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="7"><p class="ty-no-items">{__("text_no_orders")}</p></td>
        </tr>
    {/foreach}
</table>

{include file="common/pagination.tpl"}

{capture name="mainbox_title"}{__("orders")}{/capture}
