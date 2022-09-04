(function(_, $) {
    var methods = {
        init: function init() {
            $('[data-ca-calendar-delivery-auto-save-on-change="true"]').on('change', function(context) {
                var $input = $(this);
                var fieldName = $input.data('caCalendarDeliveryField');
                params = {
                    dispatch: 'checkout.customer_info'
                };
                params[fieldName] = $input.val();
                $.ceAjax('request', fn_url(''), {
                    method: 'post',
                    caching: false,
                    hidden: true,
                    data: params,
                    full_render: true,
                    result_ids: 'litecheckout_final_section,checkout*',
                });
            });
        }
    }

    $.extend({
        ceCalendarDelivery: function ceCalendarDelivery(method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('ty.ceCalendarDelivery: method ' + method + ' does not exist');
            }
        }
    });

    $(document).ready(function () {
        $.ceCalendarDelivery('init');
    });
}(Tygh, Tygh.$));
