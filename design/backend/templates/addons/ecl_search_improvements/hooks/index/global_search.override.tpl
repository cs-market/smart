<form id="global_search" method="get" action="{""|fn_url}" class="search__form">
    <input type="hidden" name="dispatch" value="search.results" />
    <input type="hidden" name="compact" value="Y" />
    <input id="elm_match_field" type="hidden" name="match" value="{$addons.ecl_search_improvements.admin_search_type}" />
    <label for="gs_text" class="search__group">
        <input type="text" class="cm-autocomplete-off search__input" id="gs_text" name="q" placeholder="{__("search")}" value="{$smarty.request.q}" />
        <button class="btn search__button" type="submit" id="search_button"></button>
    </label>
</form>
