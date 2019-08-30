(function (_, $) {

    var is_map_script_loaded = false;
    var regions = [];

    var methods = {
        /**
         *
         * @param {jQuery} $elm
         */
        init: function ($elm) {
            methods.auto_detect(methods.set_location_async, $elm);
        },

        /**
         *
         * @param {Function} callback
         * @param {jQuery} $elm
         */
        auto_detect: function (callback, $elm) {
            ymaps.ready(function () {
                ymaps.geolocation.get({
                    provider: location.protocol === 'https' ? 'auto' : 'yandex'
                }).then(function (result) {
                    var geo_object = result.geoObjects.get(0);
                    methods.get_location(geo_object, callback, $elm);
                });
            });
        },

        /**
         *
         * @param {Object} geo_object
         * @param {Function} callback
         * @param {jQuery} $elm
         */
        get_location: function (geo_object, callback, $elm) {
            var meta = geo_object.properties.get('metaDataProperty').GeocoderMetaData.Address;

            var location = {
                country: meta.country_code,
                countryName: null,
                state: null,
                stateName: null,
                city: null
            };

            for (var i = 0; i < meta.Components.length; i++) {
                var component = meta.Components[i];
                switch (component.kind) {
                    case 'country': {
                        location.countryName = component.name;
                        break;
                    }
                    case 'province': {
                        location.stateName = component.name;
                        break;
                    }
                    case 'locality': {
                        location.city = component.name;
                        break;
                    }
                }
            }

            var detectState = function () {
                for (var i = 0; i < regions[location.country].features.length; i++) {
                    var region = regions[location.country].features[i].properties;
                    if (region.name === location.stateName) {
                        location.state = region.iso3166.split('-').pop();
                        break;
                    }
                }

                callback(location, geo_object, $elm);
            };

            if (regions[location.country]) {
                detectState();
            } else {
                ymaps.borders.load(location.country, {
                    quality: 0
                }).then(function (geojson) {
                    regions[location.country] = geojson;
                    detectState();
                }, function (fail) {
                    callback(location, geo_object, $elm);
                });
            }
        },

        /**
         *
         * @param {Object} location
         * @param {Object} geo_object
         * @param {jQuery} $container
         * @param {boolean} reload
         */
        set_location: function (location, geo_object, $container, reload) {
            location.country = location.country || '';
            location.state = location.state || '';
            location.city = location.city || '';
            if (typeof reload === 'undefined') {
                reload = true
            }

            $.ceAjax('request', fn_url('rus_customer_geolocation.set_location'), {
                method: 'post',
                data: {
                    country: location.country,
                    state: location.state,
                    city: location.city
                },
                hidden: true,
                caching: false,
                callback: function (response) {
                    $container.each(function (i, elm) {
                        var $elm = $(elm);
                        $('[data-ca-rus-customer-geolocation-element="location"]', $elm).text(response.city);
                        $elm.data('caRusCustomerGeolocationIsLocationDetected', true);
                    });
                    if (reload) {
                        window.location.reload(true);
                    }
                }
            });
        },

        /**
         *
         * @param {Object} location
         * @param {Object} geo_object
         * @param {jQuery} $container
         */
        set_location_async: function (location, geo_object, $container) {
            methods.set_location(location, geo_object, $container, false);
        },

        /**
         *
         * @param {HTMLElement} elm
         */
        init_map: function (elm) {
            var $set_location = $(elm).closest('[data-ca-rus-customer-geolocation-element="location_selector"]').find(
                '.ty-rus-customer-geolocation__set-location'),
                map,
                coordinates;

            methods.auto_detect(function (location, geo_object, $container) {
                coordinates = geo_object.geometry.getCoordinates();
                map = new ymaps.Map(elm, {
                    center: coordinates,
                    zoom: 10,
                    controls: []
                }, {
                    suppressMapOpenBlock: true
                });

                map.geoObjects.add(methods.get_placemark(coordinates, $set_location));

                var search = new ymaps.control.SearchControl({
                    options: {
                        float: 'left',
                        fitMaxWidth: true,
                        kind: 'locality',
                        maxWidth: [30, 72, 660],
                        suppressYandexSearch: true
                    }
                });

                search.events.add('resultshow', function (e) {
                    var result = search.getResultsArray()[e.get('index')];

                    setTimeout(function () {
                        search.hideResult();
                    }, 0);

                    map.geoObjects.removeAll();

                    coordinates = result.geometry.getCoordinates();

                    map.geoObjects.add(methods.get_placemark(coordinates, $set_location));
                });

                map.controls.add(search);

                $set_location.removeClass('pending');
            }, $(elm));

            $set_location.click(function (e) {
                if ($(this).is('pending')) {
                    return false;
                }

                ymaps.geocode(coordinates).then(function (result) {
                    var geo_object = result.geoObjects.get(0);
                    methods.get_location(
                        geo_object,
                        methods.set_location,
                        $('[data-ca-rus-customer-geolocation-element="location_block"]')
                    );
                });
            });
        },

        /**
         *
         * @param {jQuery} $elm
         */
        init_city_selector: function ($elm) {
            $elm.click(function (e) {
                e.preventDefault();
                methods.set_location({
                    country: $elm.data('caRusCustomerGeolocationLocationCountry'),
                    state: $elm.data('caRusCustomerGeolocationLocationState'),
                    city: $elm.data('caRusCustomerGeolocationLocationCity')
                }, null, $('[data-ca-rus-customer-geolocation-element="location_block"]'));
            });
        },

        /**
         *
         * @param {Number[]|Object|IPointGeometry} coordinates
         * @param {jQuery} $set_location
         * @returns {ymaps.Placemark}
         */
        get_placemark: function (coordinates, $set_location) {
            var placemark = new ymaps.Placemark(coordinates, {}, {
                preset: 'islands#redDotIcon'
            });

            placemark.events.add('click', function () {
                $set_location.trigger('click');
            });

            return placemark;
        },

        /**
         *
         * @param {jQuery} $elm
         */
        show_map_load_error: function ($elm) {
            $elm.closest('[data-ca-rus-customer-geolocation-element="location_selector"]')
                .find('[data-ca-rus-customer-geolocation-element="map_load_error_message"]')
                .removeClass('hidden');
            $elm.addClass('hidden');
            $('.ty-rus-customer-geolocation__set-location').removeClass('pending');
        }
    };

    $.extend({
        ceRusCustomerGeolocation: function (method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('ty.rus-customer-geolocation: method ' + method + ' does not exist');
            }
        }
    });

    $.ceEvent('on', 'ce.commoninit', function (context) {
        var location_blocks = $('[data-ca-rus-customer-geolocation-element="location_block"]', context),
            maps = $('[data-ca-rus-customer-geolocation-element="map"]', context),
            city_selectors = $('[data-ca-rus-customer-geolocation-element="city"]', context);

        var map_script_load_callback = function () {

            is_map_script_loaded = true;

            if (location_blocks.length) {
                location_blocks.each(function (i, elm) {
                    var $elm = $(elm);
                    if (!$elm.data('caRusCustomerGeolocationIsLocationDetected')) {
                        $.ceRusCustomerGeolocation('init', $elm);
                    }
                });
            }

            if (maps.length) {
                maps.each(function (i, elm) {
                    $.ceRusCustomerGeolocation('init_map', elm);
                });
            }

            if (city_selectors.length) {
                city_selectors.each(function (i, elm) {
                    $.ceRusCustomerGeolocation('init_city_selector', $(elm));
                });
            }
        };

        if (is_map_script_loaded) {
            map_script_load_callback();
        } else {
            var load_result = $.getScript('https://api-maps.yandex.ru/2.1/?lang=ru_RU', function () {
                map_script_load_callback();
            });

            if (load_result === false) {
                maps.each(function (i, elm) {
                    var $elm = $(elm);
                    $.ceRusCustomerGeolocation('show_map_load_error', $elm);
                });
            }
        }
    });
})(Tygh, Tygh.$);