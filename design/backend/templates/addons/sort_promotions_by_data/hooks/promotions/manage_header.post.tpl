<th width="10%" class="nowrap center mobile-hide">
    <a class="cm-ajax" href="{"`$c_url`&sort_by=from_date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("sort_promotions_by_data__available_from")}{if $search.sort_by == "priority"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
<th width="10%" class="nowrap center mobile-hide">
    <a class="cm-ajax" href="{"`$c_url`&sort_by=to_date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("sort_promotions_by_data__available_to")}{if $search.sort_by == "priority"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
