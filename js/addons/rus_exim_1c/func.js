(function(_, $) {
    function fn_change_option_class() {
        if ($('#addon_option_rus_exim_1c_exim_1c_import_mode_offers').val() == 'standart' || $('#addon_option_rus_exim_1c_exim_1c_import_mode_offers').val() == 'standart_general_price') {
            $('#container_addon_option_rus_exim_1c_exim_1c_import_option_name').removeClass('hidden');
        } else {
            $('#container_addon_option_rus_exim_1c_exim_1c_import_option_name').addClass('hidden');
        }
    }

    $.ceEvent('on', 'ce.commoninit', function(context) {
        $('#container_addon_option_rus_exim_1c_exim_1c_import_mode_offers').on('click', function () {
            fn_change_option_class();
        });
    });

    $(document).ready(function() {
        fn_change_option_class();
    });
}(Tygh, Tygh.$));