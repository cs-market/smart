// Bulk edit
(function (_, $) {

    var _doc = $(document);

    $.ceEvent('on', 'ce.commoninit', _bulkEditInit);

    $.ceEvent('on', 'ce.tap.toggle', function (selected) {
        (selected ? $('.bulkedit-toggler') : $('.bulkedit-disabler')).trigger('click');
    });

    $.ceEvent('on', 'ce.select_template_selection', function (object, list_elm, $container) {
        if (!$container.hasClass('cm-bulk-edit-object-categories-add') || !object.data) {
            return;
        }

        $(list_elm).toggleClass('no-bold', true);
    });

    /**
     * Init function, binds events
     */
    function _bulkEditInit (context) {
        if (!$(context).find('[data-ca-bulkedit-expanded-object="true"]').length) {
            return;
        }

        _doc.on('click', '.bulkedit-toggler', toggleBulkEditPanel);
        _doc.on('click', '.bulkedit-disabler', toggleBulkEditPanel);

        var dispatchParameter = $('.bulkedit-toggler').data('caBulkeditDispatchParameter');
        if (dispatchParameter) {
            _doc.on('click', '.bulkedit-toggler', setDispatchParameterBulkEditBtn);
        }
    }

    /**
     * Toggling bulk edit panel
     * @param {Event} event 
     */
    function toggleBulkEditPanel (event) {
        var $self    = $(this),
            $enable  = $( $self.data('caBulkeditEnable') ),
            $disable = $( $self.data('caBulkeditDisable') );

        $enable.removeClass('hidden');
        $disable.addClass('hidden');

        $('[name="check_all"]').prop('checked', false);
    }

    /**
     * Add selected ids as a parameter in dispatch
     * @param {Event} event 
     */
    function setDispatchParameterBulkEditBtn (event) {
        var $self = $(this),
            parametrElmName = $self.data('caBulkeditDispatchParameter'),
            ids = [];

        $('[name="' + parametrElmName +'"]:checked').each(function(){
            ids.push($(this).val());
        });

        $('.bulk-edit [data-ca-pass-selected-object-ids-as]').each(function(){
            var dispatch = $(this).data('caDispatch');

            if (dispatch && ids.length > 0) {
                dispatch = dispatch.replace(']', '&' + $(this).data('caPassSelectedObjectIdsAs') + '={' + ids + '}');
                $(this).attr('data-ca-dispatch', dispatch);
            }
        });
    }

})(Tygh, Tygh.$);

// Bulk edit => Custom tristate checkbox
(function (_, $) {
    $(document).on('click', '.cm-readonly', function (e) {
        e.preventDefault();
    });

    $.ceEvent('on', 'ce.commoninit', function (context) {
        $('[data-set-indeterminate="true"]', $(context)).prop('indeterminate', true);
    });

    $(document).on('mouseup', '.cm-tristate', function (e) {
        e.preventDefault();
        var scope = this;

        setTimeout(function () {
            _onclick.call(scope);
        }, 1);
    });

    function _onclick () {
        if ($(this).data('caTristateJustClick')) {
            return;
        }

        var elm = $(this).get(0);

        if (elm.readOnly) elm.checked = elm.readOnly = false;
        else if (!elm.checked) elm.readOnly = elm.indeterminate = true;
    }
})(Tygh, Tygh.$);



// Bulk edit => Custom dropdown
(function (_, $) {
    $(document).on('click', '.bulk-edit-toggle', function () {
        $('.bulk-edit--overlay').remove();
        $('body').append($('<div class="bulk-edit--overlay"></div>'));

        $( $(this).data('toggle') ).toggleClass('open', true);

        var scope = this;

        $('.bulk-edit--overlay').one('click', function () {
            $('.bulk-edit--overlay').remove();

            $( $(scope).data('toggle') ).toggleClass('open', false);
        });
    });

    $(document).on('click', '.cm-toggle', function (e) {
        var self = $(this);

        if (self.data('state') == 'show') {
            self.data('state', 'hide');
            self.html( self.data('hideText') );
            $(self.data('toggle')).toggleClass('hidden', false);
        } else {
            self.data('state', 'show');
            self.html( self.data('showText') );
            $(self.data('toggle')).toggleClass('hidden', true);
        }

        e.preventDefault();
    });
})(Tygh, Tygh.$);