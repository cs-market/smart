<form id="global_search" method="get" action="{""|fn_url}">
    <input type="hidden" name="dispatch" value="search.results" />
    <input type="hidden" name="compact" value="Y" />
    <input id="elm_match_field" type="hidden" name="match" value="{$addons.ecl_search_improvements.admin_search_type}" />
    <button class="icon-search cm-tooltip " type="submit" title="{__("search_tooltip")}" id="search_button"></button>
    <label for="gs_text"><input type="text" class="cm-autocomplete-off" id="gs_text" name="q" value="{$smarty.request.q}" /></label>
</form>