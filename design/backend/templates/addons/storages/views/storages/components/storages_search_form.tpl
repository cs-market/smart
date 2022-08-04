<div class="sidebar-row">
    <h6>{__("search")}</h6>
    {script src="js/tygh/filter_table.js"}
    <form action="{""|fn_url}" name="storages_search_form" method="get" class="{$form_meta} storages-search-form addons-search-form">
        {$extra nofilter}
        
        <div class="sidebar-field ">
            <label for="elm_storage">{__("name")}</label>
            <input type="text" name="q" id="elm_storage" value="{$search.q}" size="30" autofocus />
            <i class="icon icon-remove hidden" id="elm_storage_clear" title="{__("remove")}"></i>
        </div>

        <div class="sidebar-field">
            <input class="btn" type="submit" name="dispatch[{$dispatch}]" value="{__("search")}">
        </div>
    </form>
</div>
