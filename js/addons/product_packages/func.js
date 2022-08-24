(function(_, $) {
    var methods = {
        bindControls: function (location, $container) {
            $('.cm-switcher-control').click(function(e) {
                elm = $(this)
                checkbox = $('#' + elm.data('caTarget'));
                checkbox.prop('checked', elm.data('caState')).change();
            });
        }
    }

    $.extend({
        ceSimpleSwitcher: function (method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('ceSimpleSwitcher: method ' + method + ' does not exist');
            }
        }
    });

    $.ceEvent('on', 'ce.commoninit', function() {
        $.ceSimpleSwitcher('bindControls');
    });
    $.ceEvent('on', 'ce.ajaxdone', function() {
        $.ceSimpleSwitcher('bindControls');
    });
}(Tygh, Tygh.$));
