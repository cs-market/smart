(function (_, $) {

    var methods = {
        rebuild: function () {
            var $location_rows = $('.ty-rus-customer-geolocation-location');
            $location_rows.each(function (i, elm) {
                var $row = $(elm);
                if ($row.hasClass('cm-delete-row')) {
                    return;
                }

                i = i + 1;

                var $country = $('.ty-rus-customer-geolocation-location__country', $row);
                $country
                    .prop('id', 'elm_country_' + i)
                    .prop('name', 'locations[' + i + '][country]')
                    .prop('class', $country.prop('class').replace(/cm-location-\d+/, 'cm-location-' + i));
                var hasCountry = $country.val() !== '';

                var $stateS = $('.ty-rus-customer-geolocation-location__state--select', $row),
                    $stateI = $('.ty-rus-customer-geolocation-location__state--input', $row);

                if (!hasCountry) {
                    $stateS.prop('disabled', null);
                    $stateI.prop('disabled', 'disabled');
                }

                var stateSDisabled = hasCountry && $stateS.prop('id').substr(-2) === '_d',
                    stateIDIsabled = !stateSDisabled;
                $stateS
                    .prop('id', 'elm_state_' + i + (stateSDisabled ? '_d' : ''))
                    .prop('name', 'locations[' + i + '][state]')
                    .prop('class', $stateS.prop('class').replace(/cm-location-\d+/, 'cm-location-' + i));
                $stateI
                    .prop('id', 'elm_state_' + i + (stateIDIsabled ? '_d' : ''))
                    .prop('name', 'locations[' + i + '][state]')
                    .prop('class', $stateI.prop('class').replace(/cm-location-\d+/, 'cm-location-' + i));

                var $city = $('.ty-rus-customer-geolocation-location__city', $row);
                $city
                    .prop('id', 'elm_city_' + i)
                    .prop('name', 'locations[' + i + '][city]');

                var ddHandle = $('.cm-sortable-handle', $row);
                if (i !== $location_rows.length) {
                    ddHandle.removeClass('hidden');
                    $row.addClass('cm-row-item', 'cm-sortable-row');
                }

                if (!hasCountry) {
                    $country.trigger('change');
                }
            });
        }
    };

    $.extend({
        ceRusCustomerGeolocationLocationsList: function (method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('ty.rus-customer-geolocation-locations-list: method ' + method + ' does not exist');
            }
        }
    });

    $.ceEvent('on', 'ce.formpre_rus_customer_geolocation_update_locations', function (form, elm) {
        $.ceRusCustomerGeolocationLocationsList('rebuild');
    });
})(Tygh, Tygh.$);