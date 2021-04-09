(function(_, $) {
    var handlers = {
        showObjects: function(request, limit) {
            var $self = this;

            csymaps.geocode(request, { results: limit || 10 }).then(function(res) {
                var coords = res.geoObjects.get(0).geometry.getCoordinates();
                map = new csymaps.Map($self.get(0), {
                    center: coords,
                    zoom: 13,
                });
                map.behaviors.disable(['scrollZoom']);
                map.geoObjects.add(res.geoObjects);
            });
        },

        init: function(settings, callback) {
            var default_settings = {
                key: _.maps_provider.yandex_key,
                lang: _.cart_language,
            };

            settings = $.extend(default_settings, settings);

            var url = 'https://api-maps.yandex.ru/2.1/?ns=csymaps&lang=ru_RU';

            if (settings.key) {
                url += '&key=' + settings.key;
            }

            $.getScript(url, function() {
                csymaps.ready(function() {
                    callback();
                });
            });
        },

        getUserLocation: function(callback) {
            var options = {provider: location.protocol === 'https' ? 'auto' : 'yandex'}
            csymaps.geolocation.get(options)
                .then(handlers.extractUserLocation)
                .then(handlers.getLocationStateCode)
                .then(callback);
        },

        extractUserLocation: function (result) {
            var geo_object = result.geoObjects.get(0),
                meta = geo_object.properties.get('metaDataProperty').GeocoderMetaData.Address,
                coords = geo_object.geometry.getCoordinates(),
                location = {
                    lat: coords[0],
                    lng: coords[1],
                    counry: '', // mistyped kept for backward compatibility
                    country: '',
                    country_code: meta.country_code,
                    state: '',
                    state_code: '',
                    city: '',
                    address: meta.formatted || '',
                };

            for (var i = 0; i < meta.Components.length; i++) {
                var component = meta.Components[i];
                switch (component.kind) {
                    case 'country': {
                        location.country = location.counry = component.name;
                        break;
                    }
                    case 'province': {
                        location.state = component.name;
                        break;
                    }
                    case 'locality': {
                        location.city = component.name;
                        break;
                    }
                }
            }

            return location;
        },

        getLocationStateCode: function (location) {
            var d = $.Deferred();

            csymaps.borders.load(location.country_code, {
                quality: 0
            }).then(function (geojson) {
                location.state_code = handlers.extractStateCode(geojson, location);
                d.resolve(location);
            }, function () {
                d.resolve(location);
            });

            return d.promise();
        },

        extractStateCode: function (geojson, location) {
            var state_code = '';
            for (var i = 0; i < geojson.features.length; i++) {
                var region = geojson.features[i].properties;

                // HOTFIX: YMaps JS API bug fix, remove this when borders.load starts returning name-field such as location stateName-field
                var stateNameEquals = (('Республика ' + region.name) === location.state);

                if ((region.name === location.state) || stateNameEquals) {
                    state_code = region.iso3166.split('-').pop();
                    break;
                }
            }

            return state_code;
        },
    };

    $.ceMap('handlers', handlers);

}(Tygh, Tygh.$));
