{script src="js/addons/infinite_pagination/ce_infinite_pagination.js"}
{script src="js/addons/infinite_pagination/func.js"}

<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    _.tr({
        infinite_pagination_more: '{__("infinite_pagination__more")}',
        infinite_pagination_load_automatically: '{__("infinite_pagination__load_automatically")}',
        infinite_pagination_loaded: '{__("infinite_pagination__loaded")}'
    });
    $.extend(_, {
        infinite_pagination: {
            more_pages: "{$addons.infinite_pagination.more_pages|default:1|escape:javascript nofilter}",
            use_more: "{$addons.infinite_pagination.use_more|default:'N'|escape:javascript nofilter}",
            use_prev: "{$addons.infinite_pagination.use_prev|default:'Y'|escape:javascript nofilter}",
            show_page: "{$addons.infinite_pagination.show_page|default:'N'|escape:javascript nofilter}",
        }
    });
}(Tygh, Tygh.$));
//]]>
</script>
