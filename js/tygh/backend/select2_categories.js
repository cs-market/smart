(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function (context) {
        var $elems = $('.cm-object-categories-add', context),
            category_ids = [];

        if ($elems.length) {
            $.each($elems, function () {
                var value = $(this).val();

                if (!value) {
                    return;
                }

                if (!Array.isArray(value)) {
                    value = [value];
                }

                category_ids = category_ids.concat(value);
            });

            if (category_ids.length) {
                fn_actualize_selected_categories_list_data(category_ids, $elems);
            }
        }
    });

    $.ceEvent('on', 'ce.change_select_list', function (object, $container) {
        if ($container.hasClass('cm-object-categories-add') && object.data) {
            object.context = object.data.content;
        }
    });

    $.ceEvent('on', 'ce.select_template_selection', function (object, list_elm, $container) {
        if ($container.hasClass('cm-object-categories-add') && object.data) {
            if (object.data.disabled) {
                $(list_elm).find('.select2-selection__choice__remove').remove();
            }

            object.context = object.data.content;
        }
    });

    // Hook add_js_items
    $.ceEvent('on', 'ce.picker_add_js_items', function (picker, items, data) {
        var $select2_selectbox = $('[data-ca-picker-id="' + data.root_id + '"]'),
            category_ids = Object.keys(items).map(function (category_id) {
                return category_id;
            });

        if (category_ids.length && $select2_selectbox.length) {
            $.map(items, function (data, category_id) {
                $.each($select2_selectbox, function (key, selectbox) {
                    var $selectbox = $(selectbox),
                        selected_ids = $selectbox.val() || null;

                    if (!Array.isArray(selected_ids)) {
                        selected_ids = [selected_ids];
                    }

                    if (selected_ids.indexOf(category_id) === -1) {
                        var option = new Option(data.category, category_id, true, true);
                        $selectbox
                            .append(option)
                            .trigger('change');
                    }
                });
            });

            fn_actualize_selected_categories_list_data(category_ids, $select2_selectbox);
        }
    });

    var fn_actualize_selected_categories_list_data = function (category_ids, $select2_selectbox)
    {
        $.ceAjax('request', fn_url('categories.get_categories_list'), {
            hidden: true,
            caching: true,
            data: {
                id: category_ids
            },
            callback: function (response) {
                var category_map = {};

                if (typeof response.objects !== 'undefined') {
                    $.each(response.objects, function (key, category) {
                        category_map[category.id] = category;
                    });

                    $.each($select2_selectbox, function (key, selectbox) {
                        var $selectbox = $(selectbox),
                            selected_ids = $selectbox.val();

                        if (!selected_ids) {
                            return;
                        }

                        if (!Array.isArray(selected_ids)) {
                            selected_ids = [selected_ids];
                        }

                        $.each(selected_ids, function (key, id) {
                            if (typeof category_map[id] !== 'undefined') {
                                var category = category_map[id],
                                    $option = $selectbox.find('option[value=' + id + ']');

                                $option.text(category.text);
                                $option.data('data', $.extend($option.data('data'), category));
                            }
                        });

                        $selectbox.trigger('change');
                    });
                }
            }
        });
    };
}(Tygh, Tygh.$));
