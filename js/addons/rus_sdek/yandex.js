(function(_, $) {
    (function($) {

        var maps = [];
        var saved_point = null;
        var map_params = [];

        var latitude = 0;
        var longitude = 0;
        var zoom = 0;

        var latitude_name = '';
        var longitude_name = '';
        var map_container = '';

        var start_init_sdekmaps = false;
        var finish_init_sdekmaps = false;

        var methods = {

            init: function(options, callback) {

                var group_key = options.group_key;
                var shipping_id = options.shipping_id;

                if (! ('sdekmaps' in window)) {

                    if (!start_init_sdekmaps) {
                        start_init_sdekmaps = true;

                        $.getScript('//api-maps.yandex.ru/2.1/?ns=sdekmaps&lang=' + options.language, function () {
                            sdekmaps.ready(function () {
                                finish_init_sdekmaps = true;
                                $.ceSdekPickup('init', options, callback);
                            });
                        });
                    } else {
                        setTimeout(function() { $.ceSdekPickup('init', options, callback)}, 500);
                    }

                    return false;
                }

                if (!start_init_sdekmaps || (start_init_sdekmaps && !finish_init_sdekmaps)) {
                    setTimeout(function() { $.ceSdekPickup('init', options, callback)}, 500);

                    return false;
                }

                latitude = options.latitude;
                longitude = options.longitude;
                map_container = options.sdek_map_container;

                zoom = options.zoom;

                // Required fields - zoom, center
                map_params[shipping_id] = {
                    zoom: 12,
                    type: 'yandex#map',
                    center: [latitude, longitude],
                    controls: ['default']
                };


                if (_.area == 'A') {
                    $.extend(map_params[shipping_id], {
                        draggableCursor: 'crosshair',
                        draggingCursor: 'pointer'
                    });
                } else {
                    $.extend(map_params[shipping_id], {
                        zoom: zoom
                    });
                }

                if (typeof(callback) == 'function') {
                    callback();
                }
            },

            destroyMaps: function(shipping_id)
            {
                maps.forEach(function(element, index) {
                    if ($('#sdek_map_' + index).length) {
                        maps[index].destroy();
                    }
                });
            },

            show: function(options)
            {
                if (typeof options == "undefined") {
                    return false;
                }

                var group_key = options.group_key;
                var shipping_id = options.shipping_id;

                if (!map_params[shipping_id]) {
                    return $.ceSdekPickup('init', options, function() {

                        $.ceSdekPickup('show', options);
                    });
                }

                if (maps[shipping_id]) {
                    $('ymaps').remove();
                    maps[shipping_id] = [];
                }

                if (!maps[shipping_id] || typeof maps[shipping_id].layers == "undefined" || !$('ymaps').length) {

                    maps[shipping_id] = new sdekmaps.Map(document.getElementById(options.sdek_map_container), map_params[shipping_id]);

                    maps[shipping_id].controls.remove('searchControl');
                    maps[shipping_id].behaviors.disable(['scrollZoom']);

                    var marker;
                    storeData = options.storeData;

                    for (var keyvar = 0; keyvar < storeData.length; keyvar++) {

                        var marker_html = '<div style="padding-right: 10px"><strong>' + storeData[keyvar]['name'];

                        marker_html += '</strong><p>';

                        if (storeData[keyvar]['city'] != '') {
                            marker_html += storeData[keyvar]['city'] + ', ';
                        }

                        if (typeof(storeData[keyvar]['pickup_address']) !== 'undefined') {
                            marker_html += storeData[keyvar]['pickup_address'];
                        }

                        if (typeof(storeData[keyvar]['pickup_phone']) !== 'undefined') {
                            marker_html += '<br/>' + storeData[keyvar]['pickup_phone'];
                        }

                        if (typeof(storeData[keyvar]['pickup_time']) !== 'undefined') {
                            marker_html += '<br/>' + storeData[keyvar]['pickup_time'];
                        }

                        if (options['selectStore'] === true) {
                            marker_html += '<p><a data-ca-shipping-id="' + storeData[keyvar]['shipping_id'] + '" data-ca-group-key="' + storeData[keyvar]['group_key'] + '" data-ca-location-id="' + storeData[keyvar]['store_location_id'] + '" class="cm-sdek-select-location ty-btn ty-btn__tertiary text-button">Выбрать</a></p>';
                        }

                        marker_html += '</p></div>';

                        marker = new sdekmaps.Placemark([storeData[keyvar]['latitude'], storeData[keyvar]['longitude']], {
                            balloonContentBody: marker_html
                        });

                        maps[shipping_id].geoObjects.add(marker);

                    }

                    if (storeData.length == 1) {

                        maps[shipping_id].setCenter(marker.geometry.getCoordinates());
                        maps[shipping_id].setZoom(zoom);

                    } else {

                        sdekmaps.geoQuery(maps[shipping_id].geoObjects).applyBoundsToMap(maps[shipping_id]);

                        var select = $('.ty-sdek-office__radio-' + group_key + ':checked').attr('value');

                        $('input.ty-sdek-office__radio-' + group_key + '[value="' + select + '"]').parent('.ty-sdek-office').addClass('ty-sdek-office__selected').show();
                        $('.ty-sdek-name-office-' + group_key + '-' + select).show();

                        if (!select) {
                            var select = $('.ty-sdek-office__radio:checked').attr('value');
                        }

                        if (select) {
                            $.each(storeData, function (key, value) {
                                if (value['store_location_id'] == select) {
                                    maps[shipping_id].setCenter([value['latitude'], value['longitude']]);
                                    maps[shipping_id].setZoom(zoom);
                                    return false;
                                }
                            });
                        }
                    }
                }
            },

            saveLocation: function()
            {
                if (saved_point) {
                    $('#' + latitude_name).val(saved_point[0]);
                    $('#' + latitude_name + '_hidden').val(saved_point[0]);
                    $('#' + longitude_name).val(saved_point[1]);
                    $('#' + longitude_name + '_hidden').val(saved_point[1]);
                }

                saved_point = null;
            },

            selectLocation: function(location, group_key, shipping_id)
            {
                if (maps[shipping_id]) {
                    maps[shipping_id].destroy();
                }
                
                $('#office_' + group_key + '_' + shipping_id + '_' + location).prop("checked", true);
        
                fn_calculate_total_shipping_cost();
            },

            viewLocation: function(latitude, longitude, group_key)
            {
                maps[shipping_id].setCenter([latitude, longitude]);
                maps[shipping_id].setZoom(zoom);
            },

            viewLocations: function(shipping_id)
            {
                sdekmaps.geoQuery(maps[shipping_id].geoObjects).applyBoundsToMap(maps[shipping_id]);
            }
        };

        $.extend({
            ceSdekPickup: function(method) {
                if (methods[method]) {
                    return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
                } else {
                    $.error('ty.map: method ' +  method + ' does not exist');
                }
            }
        });
    })($);

    $(document).ready(function() {

        $(document).on('click', '.cm-sdek-select-store', function(e) {
            $.ceSdekPickup('destroyMaps');

            fn_calculate_total_shipping_cost();
        });

        $(document).on('click', '.cm-sdek-save-location', function () {
            $.ceSdekPickup('saveLocation');
        });

        $(document).on('click', '.cm-sdek-select-location', function () {
            var jelm = $(this);
            var location = jelm.data('ca-location-id');
            var group_key = jelm.data('ca-group-key');
            var shipping_id = jelm.data('ca-shipping-id');

            $('.ty-sdek-checkout-select-office input[type=radio]:checked').val(location);

            $.ceSdekPickup('selectLocation', location, group_key, shipping_id);
        });

        $(document).on('click', '.cm-sdek-view-location', function () {
            var jelm = $(this);
            var latitude = jelm.data('ca-latitude');
            var longitude = jelm.data('ca-longitude');
            var shipping_id = jelm.data('ca-shipping-id');

            $.ceSdekPickup('viewLocation', latitude, longitude, shipping_id);

            if ($(this).data('ca-scroll')) {
                var id = $(this).data('ca-scroll');
                $.scrollToElm(id);
            }
        });

        $(document).on('click', '.cm-show-all-point', function(e) {
            var pickpoints = $('.ty-sdek-office');
            var group_key = $(this).data('ca-group-key');
            var shipping_id = $(this).data('ca-shipping-id');

            $.ceSdekPickup('viewLocations', shipping_id);

            $(this).hide();
            $.each(pickpoints, function( key, value ) {
                $(value).show();
                $('.ty-sdek-office-search').show();
                $('.ty-sdek-checkout-select-office').addClass('ty-sdek-list-office');
            });

            if ($(this).data('ca-scroll')) {
                var id = $(this).data('ca-scroll');
                $.scrollToElm(id);
            }
        });

    });
}(Tygh, Tygh.$));
