(function(_, $) {
    $.ceEvent('on', 'ce.product_option_changed', function (obj_id, id, option_id, update_ids) {
        if ($('#configurable_product_tabs_' + id).length) {
            update_ids.push('configurable_product_tabs_' + id);
        }
    });
}(Tygh, Tygh.$));
