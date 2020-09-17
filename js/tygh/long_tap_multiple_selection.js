/**
 * Enable multiple selection in admin.
 */

(function (_, $) {

    $.ceEvent('on', 'ce.commoninit', function () {
        var setCheckboxFlag = function (selfObj, targetSelector, flag) {
            selfObj
                .find(targetSelector)
                .each(function (index, elm) {
                    elm.checked = flag;
                });
        }

        // initialize plugin
        var longtap = $('[data-ca-longtap-action]').ceLongtap({
            timeout: 700,
            onStartDelay: 250,
            allowQuickMode: true,

            onStart: function (event, self) {
                self.addClass('long-tap-start');
            },

            onSuccess: function (event, self) {
                self.removeClass('long-tap-start');
                self.addClass('selected');

                if (self.data().caLongtapAction == 'setCheckBox') {
                    setCheckboxFlag(self, self.data().caLongtapTarget, true);
                }
            },

            onStop: function (event, self) {
                self.removeClass('long-tap-start');
            },

            onReject: function (event, self) {
                self.removeClass('long-tap-start');
                self.removeClass('selected');

                if (self.data().caLongtapAction == 'setCheckBox') {
                    setCheckboxFlag(self, self.data().caLongtapTarget, false);
                }
            }
        });

        // select an object if it has already been selected
        var reSelect = function () {
            $('[data-ca-longtap-action]').each(function (index, item) {
                var $self = $(item);

                if ($self.data().caLongtapAction == 'setCheckBox') {
                    var checkboxSelector = $self.data().caLongtapTarget;
                    var $checkbox = $self.find(checkboxSelector);

                    var checked = $checkbox.prop('checked');

                    if (checked) {
                        longtap.selectObject(index);
                    } else {
                        if ($self.hasClass('selected')) {
                            longtap.rejectObject(index);
                        }
                    }
                }
            });
        }

        reSelect();

        $.ceEvent('on', 'ce.cm_cancel.clean_form', function (form, jelm) {
            reSelect();
        });
    });

})(Tygh, Tygh.$);