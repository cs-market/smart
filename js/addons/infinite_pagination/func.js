(function(_, $) {

    $.ceEvent('on', 'ce.commoninit',
        function(context) {

            if (!_.infinite_pagination) {
                _.infinite_pagination = {};
            }

            const convertToBool = function(data) {
                return data == 'Y';
            }

            const defaultData = {
                morePage: _.infinite_pagination.more_pages || 3,
                useMore: convertToBool(_.infinite_pagination.use_more) || false,
                usePrev: convertToBool(_.infinite_pagination.use_prev) || true,
                showPage: convertToBool(_.infinite_pagination.show_page) || true,
            };

            $.ceInfinitePagination('start', defaultData, context);
    });

}(Tygh, Tygh.$));
