{capture name="mainbox"}

<form action="{""|fn_url}" method="post" target="" name="carts_list_form">

{include file="common/pagination.tpl" save_current_url=true}

{$c_url = $config.current_url|fn_query_remove:"sort_by":"sort_order"}
{$has_permission_update = fn_check_permissions("cart", "delete", "admin", "POST")}

{if $returns}

<div class="table-responsive-wrapper longtap-selection">
    <table class="table table-middle table-responsive">
    <thead>
        <tr>
            <th width="30%">
                <a class="cm-ajax{if $search.sort_by == "customer"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("customer")}</a>
            </th>
            <th width="15%"><a class="cm-ajax{if $search.sort_by == "date"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}</a></th>
            <th width="45%">{__("return_items")}</th>
            {hook name="cart:items_list_header"}
            {/hook}
            <th width="8%" class="mobile-hide">&nbsp;</th>
        </tr>
    </thead>
    {foreach $returns as $return}
    <tr>
        <td width="30%" data-th="{__("customer")}">
            <div class="return__customer">
                <div class="return__customer-data-wrapper">
                    <div class="return__customer-data">
                        <a href="{"profiles.update&user_id=`$return.user_id`"|fn_url}">{$return.user.firstname} {$return.user.lastname}</a>
                        {if $return.company_id}
                            <p class="muted">{$return.company_id|fn_get_company_name}</p>
                        {/if}
                    </div>
                </div>
            </div>
        </td>
        <td width="15%" data-th="{__("date")}">
            {$return.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
        </td>
        <td width="45%" data-th="{__("cart")}">
            <table class="table table-condensed table--relative table-responsive">
                <thead>
                    <tr class="no-hover">
                        <th width="80%">{__("product")}</th>
                        <th class="center nowrap">{__("qty")}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$return.items item=product}
                    <tr>
                        <td data-th="{__('product')}" class="products-name">
                            <a href="{"products.update&product_id=`$product.product_id`"|fn_url}">{$product.product}</a>
                        </td>
                        <td data-th="{__('qty')}" class="center">{$product.amount}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </td>
        {hook name="cart:items_list"}
        {/hook}
        <td width="8%" class="center" data-th="{__("tools")}">
            <div class="hidden-tools">
                <div class="btn-group">
                    {if $return.file_exists}
                        {include file="buttons/button.tpl" but_role="action" but_text=__("get_file") but_href="returns.get_file?return_id=`$return.return_id`" but_meta="cm-post"}
                    {else}
                        {include file="buttons/button.tpl" but_role="action" but_text=__("export_to_file") but_href="returns.export?return_id=`$return.return_id`" but_meta="cm-post"}
                    {/if}
                </div>
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

{include file="common/mainbox.tpl"
    title=__("returns")
    content=$smarty.capture.mainbox
    no_sidebar=true
}
