<div class="ty-sort-container">
{if !$config.tweaks.disable_dhtml}
    {assign var="ajax_class" value="cm-ajax"}
{/if}

{assign var="curl" value=$config.current_url|fn_query_remove:"sort_by":"sort_order":"result_ids":"layout"}
{assign var="sorting" value=""|fn_get_products_sorting}
{assign var="sorting_orders" value=""|fn_get_products_sorting_orders}
{assign var="layouts" value=""|fn_get_products_views:false:false}
{assign var="pagination_id" value=$id|default:"pagination_contents"}
{assign var="avail_sorting" value=$settings.Appearance.available_product_list_sortings}

{if $search.sort_order_rev == "asc"}
    {capture name="sorting_text"}
        <a>{$sorting[$search.sort_by].description}<i class="ty-icon-up-dir"></i></a>
    {/capture}
{else}
    {capture name="sorting_text"}
        <a>{$sorting[$search.sort_by].description}<i class="ty-icon-down-dir"></i></a>
    {/capture}
{/if}

{if !(($category_data.selected_views|count == 1) || ($category_data.selected_views|count == 0 && ""|fn_get_products_views:true|count <= 1)) && !$hide_layouts}
<div class="ty-sort-container__views-icons">
{foreach from=$layouts key="layout" item="item"}
{if ($category_data.selected_views.$layout) || (!$category_data.selected_views && $item.active)}
    {if $layout == $selected_layout}
        {$sort_order = $search.sort_order_rev}
    {else}
        {$sort_order = $search.sort_order}
    {/if}
<a class="ty-sort-container__views-a {$ajax_class} {if $layout == $selected_layout}active{/if}" data-ca-target-id="{$pagination_id}" href="{"`$curl`&sort_by=`$search.sort_by`&sort_order=`$sort_order`&layout=`$layout`"|fn_url}" rel="nofollow">
    <i class="ty-icon-{$layout|replace:"_":"-"}"></i>
</a>
{/if}
{/foreach}
</div>
{/if}

{if $avail_sorting}
    {include file="common/sorting.tpl"}
{/if}
</div>
