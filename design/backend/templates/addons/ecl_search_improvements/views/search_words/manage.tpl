{capture name="mainbox"}

{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $search_words}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="50%"><a class="cm-ajax" href="{"`$c_url`&sort_by=product&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}><a class="{$ajax_class}" href="{"`$c_url`&sort_by=key_word&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("search_string")}{if $search.sort_by == "key_word"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("date")}{if $search.sort_by == "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=popularity&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("popularity")}{if $search.sort_by == "popularity"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
</tr>
</thead>
{foreach from=$search_words item=sw}

<tr class="cm-row-status-{$product.status|lower} {$hide_inputs_if_shared_product}">
    <td><a href="{"products.search?q=`$sw.key_word`&search_performed=Y"|fn_url:'C'}" target="_blank">{$sw.key_word}</a>&nbsp;{include file="views/companies/components/company_name.tpl" object=$sw simple=true}</td>
    <td>{$sw.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td>{$sw.popularity}</td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

<div class="clearfix">
    {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
</div>

{/capture}

{capture name="buttons"}
    {if $search_words}
         {include file="buttons/button.tpl" but_href="search_words.reset" but_text=__('reset') but_role="action" but_meta="btn-primary cm-ajax" but_target_id=$rev}
    {/if}
{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
    <form action="{""|fn_url}" method="get" name="search_words_form">
    <h6>{__("search")}</h6>
        {capture name="simple_search"}
            {include file="common/period_selector.tpl" period=$search.period display="form"}
        {/capture}
        {include file="common/advanced_search.tpl" no_adv_link=true simple_search=$smarty.capture.simple_search not_saved=true dispatch="search_words.manage"}
    </form>
    </div>
{/capture}

{include file="common/mainbox.tpl" title=__("search_words") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}