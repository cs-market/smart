(function (_, $) {
    var methods = {
        apiUrl: 'https://maps.googleapis.com/maps/api/geocode/json',

        identifyCurrentLocation: function () {
            return methods.identifyCurrentPositionByBrowser()
                .then(null, methods.identifyCurrentPositionByApi)
                .then(methods.loadLocationDataByLatLng)
                .then(methods.loadNormalizedLocationData);
        },

        identifyCurrentLocality: function (location) {
            if (!location.locality_place_id) {
                return $.Deferred().reject().promise();
            }

            return methods.loadLocationDataByPlaceId(location.locality_place_id)
                .then(methods.loadNormalizedLocationData);
        },

        saveCurrentLocation: function (location) {
            methods.saveToLocalSession('vendor_locations.' + _.vendor_locations.storage_key_geolocation, JSON.stringify(location));
            return location;
        },

        saveCurrentLocality: function (locality) {
            methods.saveToLocalSession('vendor_locations.' + _.vendor_locations.storage_key_locality, JSON.stringify(locality));
            return locality;
        },

        getCurrentLocation: function () {
            var location = methods.getFromLocalSession('vendor_locations.' + _.vendor_locations.storage_key_geolocation),
                locality = methods.getFromLocalSession('vendor_locations.' + _.vendor_locations.storage_key_locality),
                d = $.Deferred();

            if (location.place_id && locality.place_id) {
                d.resolve(location, locality);
            } else {
                methods.identifyCurrentLocation()
                    .then(function (location) {
                        methods.identifyCurrentLocality(location)
                            .then(function (locality) {
                                methods.setCurrentLocation(location, locality);
                                d.resolve(location, locality);
                            })
                            .fail(d.reject);
                    })
                    .fail(d.reject);
            }

            return d.promise();
        },

        setCurrentLocation: function (location, locality) {
            methods.saveCurrentLocation(location);
            methods.saveCurrentLocality(locality);
        },

        saveToLocalSession: function (key, value) {
            try {
                sessionStorage.setItem(key, value);
            } catch (e) {}
        },

        getFromLocalSession: function (key) {
            try {
                var value = sessionStorage.getItem(key);

                if (value) {
                    return JSON.parse(value);
                }
            } catch (e) {}

            return false;
        },

        identifyCurrentPositionByBrowser: function () {
            var d = $.Deferred();

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        d.resolve(position.coords.latitude, position.coords.longitude);
                    },
                    function (error) {
                        d.reject();
                    },
                    {
                        maximumAge: 50000,
                        timeout: 5000
                    }
                );
            } else {
                d.reject();
            }

            return d.promise();
        },

        identifyCurrentPositionByApi: function () {
            return $.post("https://www.googleapis.com/geolocation/v1/geolocate?key=" + _.vendor_locations.api_key)
                .then(function (data) {
                    return $.Deferred()
                        .resolve(data.location.lat, data.location.lng)
                        .promise();
                });
        },

        saveLocationToLocalStorage: function (place_id, location) {
            try {
                localStorage.setItem('vendor_locations.locations.' + place_id, JSON.stringify(location));
            } catch (e) {}
        },

        getLocationFromLocalStorage: function (place_id) {
            try {
                var value = localStorage.getItem('vendor_locations.locations.' + place_id);

                if (value) {
                    return JSON.parse(value);
                }
            } catch (e) {}

            return false;
        },

        convertPlaceToLocation: function (place) {
            if (typeof place.geometry.location.lat === 'function') {
                place.geometry.location.lat = place.geometry.location.lat();
            }

            if (typeof place.geometry.location.lng === 'function') {
                place.geometry.location.lng = place.geometry.location.lng();
            }
            return methods._mergeLocationResults([place]);
        },

        loadLocationDataByLatLng: function (lat, lng) {
            return $.get(methods.apiUrl, {
                key: _.vendor_locations.api_key,
                latlng: lat.toString() + ',' + lng.toString(),
                result_type: 'street_address|postal_code|locality|administrative_area_level_1|country'
            }).then(function (data) {
                return methods._mergeLocationResults(data.results);
            });
        },

        loadLocationDataByPlaceId: function (place_id) {
            return $.get(methods.apiUrl, {
                key: _.vendor_locations.api_key,
                place_id: place_id
            }).then(function (data) {
                return methods._mergeLocationResults(data.results);
            });
        },

        loadNormalizedLocationData: function (location) {
            var params = {
                    key: _.vendor_locations.api_key,
                    language: 'en'
                },
                types = null;

            if (location.type === 'country') {
                types = ['country'];
            } else if (location.type === 'administrative_area_level_1') {
                types = ['country', 'state'];
            } else if (location.type === 'locality') {
                types = ['country', 'state', 'locality'];
            }

            if ($.inArray(location.type, ['country', 'locality', 'administrative_area_level_1']) !== -1) {
                params.place_id = location.place_id;
            } else {
                params.latlng = location.lat.toString() + ',' + location.lng.toString();
                params.result_type = 'street_address|postal_code|locality|administrative_area_level_1|country';
            }

            return  $.get(methods.apiUrl, params).then(function (data) {
                var result = methods._normalizeLocation(methods._mergeLocationResults(data.results, types), location);

                if (result.type !== 'locality') {
                    var locality = methods._extractByType(data.results, 'locality');
                    result.locality_place_id = locality.place_id;
                }

                if (result.type !== 'country') {
                    var country = methods._extractByType(data.results, 'country');
                    result.country_place_id = country.place_id;
                }

                return result;
            });
        },

        base64encode: function (string) {
            return window.btoa(unescape(encodeURIComponent(string)));
        },

        loadMapApi: function () {
            if (!methods.map_api) {
                methods.map_api = $.ajax({
                    url: 'https://maps.googleapis.com/maps/api/js?key=' + _.vendor_locations.api_key + '&libraries=places',
                    dataType: "script"
                });
            }

            return methods.map_api;
        },

        _extractByType: function (locations, type) {
            var location = $(locations).filter(function (key, location) {
                return location.types && location.types[0] === type;
            });

            if (location.length) {
                return methods._mergeLocationResults(location);
            }

            return {};
        },

        _mergeLocationResults: function (results, types) {
            var result = {
                    place_id: null,
                    lat: null,
                    lng: null,
                    formatted_address: null,
                    type: null
                };

            types = types || ['country', 'state', 'locality', 'route', 'postal_code', 'street_number'];

            $.each(results, function (key, item) {
                if (!result.place_id) {
                    result.place_id = item.place_id;
                    result.formatted_address = item.formatted_address;
                    result.type = item.types[0];
                    result.lat = item.geometry.location.lat;
                    result.lng = item.geometry.location.lng;
                }

                result = $.extend(result, methods._retrieveLocationComponents(item.address_components, types));
            });

            return result;
        },

        _retrieveLocationComponents: function (components, types) {
            var result = {},
                map = {
                    country: 'country',
                    administrative_area_level_1: 'state',
                    locality: 'locality',
                    route: 'route',
                    postal_code: 'postal_code',
                    street_number: 'street_number'
                };

            $.each(components, function (key, component) {
                var type = component.types[0];

                if (map[type]) {
                    type = map[type];
                }

                if ($.inArray(type, types) !== -1) {
                    result[type] = component.short_name;
                    result[type + '_text'] = component.long_name;
                }
            });

            return result;
        },

        _normalizeLocation: function (normalized_location, location) {
            if (normalized_location.country) {
                location.country = methods._normalizeLocationCode(normalized_location.country);
                location.country_text = location.country_text || normalized_location.country_text;
            }

            if (normalized_location.state) {
                location.state = methods._normalizeLocationCode(normalized_location.state);
                location.state_text = location.state_text || normalized_location.state_text;
            }

            if (normalized_location.locality) {
                location.locality = normalized_location.locality;
                location.locality_text = location.locality_text || normalized_location.locality_text;
            }

            if (location.route && normalized_location.route) {
                location.route = normalized_location.route;
                location.route_text = location.route_text || normalized_location.route_text;
            }

            if (location.postal_code && normalized_location.postal_code) {
                location.postal_code = normalized_location.postal_code;
                location.postal_code_text = location.postal_code_text || normalized_location.postal_code_text;
            }

            if (location.street_number && normalized_location.street_number) {
                location.street_number = normalized_location.street_number;
                location.street_number_text = location.street_number_text || normalized_location.street_number_text;
            }

            return location;
        },

        _normalizeLocationCode: function (code) {
            return $.trim(code.replace(/[\s]/g, '_')).toUpperCase();
        }
    };

    $.ceGeolocate = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            $.error('ty.geolocate: method ' +  method + ' does not exist');
        }
    };

})(Tygh, Tygh.$);
