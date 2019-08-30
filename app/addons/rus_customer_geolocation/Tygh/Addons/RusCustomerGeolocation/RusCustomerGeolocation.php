<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Addons\RusCustomerGeolocation;

use Tygh\Registry;
use Tygh\Tygh;

/**
 * Class RusCustomerGeolocation provides Customer geolocation add-on functionality.
 *
 * @package Tygh\Addons\RusCustomerGeolocation
 */
class RusCustomerGeolocation
{
    const SESSION_STORAGE_KEY = 'rus_customer_geolocation_location';

    /** @var array $settings Add-on settings */
    protected $settings;

    /** @var string[] $counties_cache Countries cache */
    protected $countries;

    /** @var string $lang_code Current language */
    protected $lang_code;

    /** @var array[] $states States cache */
    protected $states;

    /** @var \Tygh\Addons\RusCustomerGeolocation\Location $location Customer location */
    protected $location;

    /** @var bool $is_detected Whether the location has been detected */
    protected $is_detected = false;

    /** @var array[] $predefined_locatons Predefined locations */
    protected $predefined_locatons;

    /** @var string[] $checkout_settings Checkout settings */
    protected $checkout_settings;

    /** @var int $company_id */
    protected $company_id;

    /** @var \Tygh\Database\Connection $db */
    protected $db;

    /**
     * RusCustomerGeolocation constructor.
     *
     * @param array                     $settings          Add-on settings
     * @param string                    $checkout_settings Checkout settings
     * @param int                       $company_id        Company ID
     * @param \Tygh\Database\Connection $db                Database connection
     * @param string                    $lang_code         Two-letter language code
     */
    public function __construct(array $settings, $checkout_settings, $db, $company_id, $lang_code)
    {
        $this->settings = $settings;
        $this->checkout_settings = $checkout_settings;
        $this->company_id = $company_id;
        $this->db = $db;
        $this->lang_code = $lang_code;
    }

    /**
     * Provides customer location.
     *
     * @return \Tygh\Addons\RusCustomerGeolocation\Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets customer location.
     *
     * @param \Tygh\Addons\RusCustomerGeolocation\Location $location
     *
     * @return \Tygh\Addons\RusCustomerGeolocation\RusCustomerGeolocation
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Checks if location was detected automatically.
     *
     * @return bool
     */
    public function getIsDetected()
    {
        return $this->is_detected;
    }

    /**
     * Sets that location was detected automatically.
     *
     * @param bool $is_detected
     *
     * @return mixed
     */
    public function setIsDetected($is_detected = true)
    {
        return $this->is_detected = $is_detected;
    }

    /**
     * Sets multiple location fields at once.
     *
     * @param string[] $location Location fields
     */
    public function setLocationFromArray(array $location)
    {
        if (isset($location['country'])) {
            $this->location->setCountry($location['country']);
        }
        if (isset($location['state'])) {
            $this->location->setState($location['state']);
        }
        if (isset($location['city'])) {
            $this->location->setCity($location['city']);
        }
        if (isset($location['zipcode'])) {
            $this->location->setZipcode($location['zipcode']);
        } else {
            $zipcode = $this->detectZipcode(
                $this->location->getCountry(),
                $this->location->getState(),
                $this->location->getCity()
            );
            if ($zipcode !== null) {
                $this->location->setZipcode($zipcode);
            }
        }
        if (isset($location['address'])) {
            $this->location->setAddress($location['address']);
        }

        if (isset($location['is_detected'])) {
            $this->setIsDetected($location['is_detected']);
        }
    }

    /**
     * Stores customer location in session, registry cache and customer profile.
     *
     * @param bool $update_profile Whether to update customer profile
     */
    public function storeLocation($update_profile = false)
    {
        $default_location = $this->location->toArray();
        $default_location['is_detected'] = $this->getIsDetected();

        fn_set_session_data(self::SESSION_STORAGE_KEY, $default_location);

        $cart_user_data = &Tygh::$app['session']['cart']['user_data'];
        $user_id = Tygh::$app['session']['auth']['user_id'];
        $location = array();

        foreach ($this->getAddressFields() as $field) {
            $this->setRuntimeLocation($field, $default_location[$field]);
            foreach (array(BILLING_ADDRESS_PREFIX . '_', SHIPPING_ADDRESS_PREFIX . '_', '') as $prefix) {
                $location[$prefix . $field] = $cart_user_data[$prefix . $field] = $default_location[$field];
            }
        }

        if ($user_id && $update_profile) {
            fn_update_user_profile($user_id, $location, 'update');
        }
    }

    /**
     * Updates location in the registry cache.
     *
     * @param string $key   Field name
     * @param string $value Field value
     */
    protected function setRuntimeLocation($key, $value)
    {
        Registry::set('settings.General.default_' . $key, $value);
    }

    /**
     * Gets localized list of countries.
     *
     * @return string[]
     */
    public function getCountries()
    {
        if ($this->countries === null) {
            $countries = fn_get_simple_countries(false, $this->lang_code);
            $allowed_countries = $this->getSchema('countries');
            foreach ($countries as $code => $name) {
                if (empty($allowed_countries[$code])) {
                    unset($countries[$code]);
                }
            }
            $this->countries = $countries;
        }

        return $this->countries;
    }

    /**
     * Gets localized list of states.
     *
     * @return array[]
     */
    public function getStates()
    {
        if ($this->states === null) {
            $this->states = fn_get_all_states(true, $this->lang_code);
        }

        return $this->states;
    }

    /**
     * Gets list of predefined locations.
     *
     * @return array[]
     */
    public function getPredefinedLocations()
    {
        if ($this->predefined_locatons === null) {
            $countries = $this->getCountries();
            $states = $this->getStates();

            $states_names = array();
            foreach ($states as $country_id => $country_states) {
                foreach ($country_states as $state) {
                    $states_names[$country_id][$state['code']] = $state['state'];
                }
            }

            $locations = $this->db->getArray(
                'SELECT * FROM ?:rus_customer_geolocation_locations'
                . ' WHERE 1=1'
                . ' ?p'
                . ' ORDER BY position ASC',
                $this->getCompanyCondition()
            );

            $hr_locations = array();
            foreach ($locations as $location) {
                $country_id = $location['country'];
                $state_id = $location['state'];

                if (!isset($hr_locations[$location['country']])) {
                    $hr_locations[$country_id] = array(
                        'title'  => $countries[$country_id],
                        'states' => array(),
                    );
                }
                if (!isset($hr_locations[$country_id]['states'][$state_id])) {
                    $hr_locations[$country_id]['states'][$state_id] = array(
                        'title'  => isset($states_names[$country_id][$state_id])
                            ? $states_names[$country_id][$state_id]
                            : $state_id,
                        'cities' => array(),
                    );
                }
                $hr_locations[$country_id]['states'][$state_id]['cities'][] = $location['city'];
            }

            $this->predefined_locatons = $hr_locations;
        }

        return $this->predefined_locatons;
    }

    /**
     * Saves list of predefined locations.
     *
     * @param array[] $locations_list
     */
    public function setPredefinedLocations(array $locations_list)
    {
        foreach ($locations_list as $i => $location) {
            if (!$location['city']
                || !$location['country']
                || !$location['state']
            ) {
                unset($locations_list[$i]);
            }
        }

        $this->predefined_locatons = null;

        $this->db->query('DELETE FROM ?:rus_customer_geolocation_locations WHERE 1=1 ?p', $this->getCompanyCondition());
        $position = 0;

        foreach ($locations_list as &$location_item) {
            $location_item['company_id'] = $this->company_id;
            $location_item['position'] = $position;
            $position += 10;
        }

        $this->db->query('INSERT INTO ?:rus_customer_geolocation_locations ?m', $locations_list);
    }

    /**
     * Gets customer location field from assorted arrays.
     *
     * @param array  $array
     * @param string $field
     *
     * @return string Field value or empty string
     */
    public function getLocationField($array, $field)
    {
        if ($this->checkout_settings['address_position'] == 'billing_first') {
            $prefixes = array('', BILLING_ADDRESS_PREFIX . '_', SHIPPING_ADDRESS_PREFIX . '_');
        } else {
            $prefixes = array('', SHIPPING_ADDRESS_PREFIX . '_', BILLING_ADDRESS_PREFIX . '_');
        }

        foreach ($prefixes as $prefix) {
            if (isset($array[$prefix . $field]) && $array[$prefix . $field] !== '') {
                return $array[$prefix . $field];
            }
        }

        return '';
    }

    /**
     * Determines zipcode using stored reference table.
     *
     * @param string $country_code ISO 3166-1 code
     * @param string $state_code   ISO 3166-2 code
     * @param string $city         City name
     *
     * @return string|null Zipcode or null when not detected
     */
    protected function detectZipcode($country_code, $state_code, $city)
    {
        /** @var int[] $city_ids */
        $city_ids = fn_rus_cities_get_city_ids($city, $state_code, $country_code);
        if ($city_ids) {
            $sdek_data = fn_rus_sdek_get_sdek_data($city_ids);
            if ($sdek_data) {
                $sdek_data = reset($sdek_data);
                $zipcode = explode(',', $sdek_data['zipcode']);
                $zipcode = reset($zipcode);

                return $zipcode;
            }
        }

        return null;
    }

    protected function getCompanyCondition()
    {
        return fn_get_company_condition('?:rus_customer_geolocation_locations.company_id', true, $this->company_id);
    }

    protected function getAddressFields()
    {
        return array('country', 'state', 'city', 'zipcode');
    }

    public function getSchema($schema_name)
    {
        return fn_get_schema('rus_customer_geolocation', $schema_name);
    }
}