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

namespace Tygh\Shippings;

use Tygh\Http;
use Tygh\Registry;
use Tygh\Shippings\Services;
use Tygh\Tygh;
use Tygh\Shippings\Shippings;

class RealtimeServices
{
    const SERVICE_NOT_CONFIGURED = 'shippings.service_not_configured';
    const SERVICE_NOT_FOUND = 'shippings.service_not_found';
    const CURRENCY_NOT_FOUND = 'shippings.currency_not_found';
    const SERVICE_NOT_ERROR = '';
    const REQUEST_ERRORS_THRESHOLD = 3;

    /**
     * Stack for registered shipping services
     *
     * @var array $_services_stack
     */
    private static $_services_stack = array();

    private static $request_data = array();

    private static $module = array();

    /**
     * Result rates
     *
     * @var array $_rates
     */
    private static $_rates = array();

    private static function _processErrorCode($code, $placeholders = array())
    {
        return __($code, $placeholders);
    }

    /**
     * Check if multireading is available on the server
     *
     * @return bool True if available, false otherwise
     */
    private static function _checkMultithreading()
    {
        if (function_exists('curl_multi_init') && Http::getCurlInfo() == '') {
            $allow_multithreading = true;
            $h_curl_multi = curl_multi_init();
            $threads = array();
        } else {
            $allow_multithreading = false;
        }

        return $allow_multithreading;
    }

    /**
     * Adds shipping service data to stack for future calculations
     *
     * @param  int   $shipping_key  Shipping service array position
     * @param  array $shipping_info Shipping service data
     * @return bool  true if information was added to stack, false otherwise
     */
    public static function register($shipping_key, $shipping_info)
    {
        if (empty($shipping_info['service_params'])) {
            return self::_processErrorCode(self::SERVICE_NOT_CONFIGURED);
        }

        $module = fn_camelize($shipping_info['module']);
        $module = 'Tygh\\Shippings\\Services\\' . $module;

        if (class_exists($module)) {
            $module_obj = new $module;

            if (isset($module_obj->calculation_currency)) {
                $currencies = Registry::get('currencies');

                if (isset($currencies[$module_obj->calculation_currency])) {
                    $shipping_info['package_info']['C'] = fn_format_price_by_currency($shipping_info['package_info']['C'], CART_PRIMARY_CURRENCY, $module_obj->calculation_currency);
                } else {
                    return self::_processErrorCode(self::CURRENCY_NOT_FOUND, array('[currency]' => $module_obj->calculation_currency));
                }
            }

            $module_obj->prepareData($shipping_info);

            self::$_services_stack[$shipping_key] = $module_obj;
            self::$module[$shipping_key] = $shipping_info['module'];
        } else {
            return self::_processErrorCode(self::SERVICE_NOT_FOUND);
        }

        return self::_processErrorCode(self::SERVICE_NOT_ERROR);
    }

    /**
     * Sends requests to real-time services and process responses.
     *
     * @return array Shipping method rates list
     */
    public static function getRates()
    {
        $_services = array(
            'multi' => array(),
            'simple' => array(),
        );

        if (empty(self::$_services_stack)) {
            return array();
        }

        if (self::_checkMultithreading()) {
            foreach (self::$_services_stack as $shipping_key => $service_object) {
                if ($service_object->allowMultithreading()) {
                    $key = 'multi';
                } else {
                    $key = 'simple';
                }

                $_services[$key][$shipping_key] = $service_object;
            }

        } else {
            $_services['simple'] = self::$_services_stack;
        }

        if (!empty($_services['multi'])) {
            foreach ($_services['multi'] as $shipping_key => $service_object) {
                self::$request_data[$shipping_key] = $data = $service_object->getRequestData();

                $headers = empty($data['headers']) ? array() : $data['headers'];
                if ($data['method'] == 'post') {
                    Http::mpost($data['url'], $data['data'], array('callback' => array('\Tygh\Shippings\RealtimeServices::multithreadingCallback', $shipping_key), 'headers' => $headers));
                } else {
                    Http::mget($data['url'], $data['data'], array(
                        'callback' => array('\Tygh\Shippings\RealtimeServices::multithreadingCallback', $shipping_key),
                        'headers' => $headers));
                }
            }

            Http::processMultiRequest();
        }

        if (!empty($_services['simple'])) {
            foreach ($_services['simple'] as $shipping_key => $service_object) {
                self::$request_data[$shipping_key] = $service_object->getRequestData();
                $response = $service_object->getSimpleRates();
                self::multithreadingCallback($response, $shipping_key);
            }
        }

        return self::$_rates;
    }

    public static function multithreadingCallback($result, $shipping_key)
    {
        $object = self::$_services_stack[$shipping_key];

        $rate = $object->processResponse($result);

        if (isset($object->calculation_currency) && $rate['cost'] !== false) {
            $rate['cost'] = fn_format_price_by_currency($rate['cost'], $object->calculation_currency, CART_PRIMARY_CURRENCY);
        }

        if (Registry::get('settings.Logging.log_type_requests.shipping') == 'Y'
            && (!isset($rate) || (isset($rate['cost']) && $rate['cost'] === false))
        ) {
            self::sendShippingErrorMessage($shipping_key, $result);
        }

        /**
         * This hook allows you to modify the processed data received from a realtime shipping service.
         *
         * @param array   $result       The result returned by the shipping service
         * @param integer $shipping_key Shipping service array position
         * @param object  $object       The object of the shipping method, the rates of which have just been calculated
         * @param array   $rate         The result of the shipping rate calculation
         */
        fn_set_hook('realtime_services_process_response_post', $result, $shipping_key, $object, $rate);

        self::$_rates[] = array(
            'price' => $rate['cost'],
            'error' => $rate['error'],
            'shipping_key' => $shipping_key,
            'delivery_time' => isset($rate['delivery_time']) ? $rate['delivery_time'] : false,
        );
    }

    /**
     * Clears shipping services stack
     */
    public static function clearStack()
    {
        self::$_services_stack = array();
        self::$_rates = array();
    }

    public function __construct()
    {

    }

    /**
     * Sends a message to administrator about a shipping method error if the error occurred more than 3 time.
     *
     * @param integer $shipping_key Shipping service array position
     * @param array   $result       The result returned by the shipping service
     *
     * @return void
     */
    private static function sendShippingErrorMessage($shipping_key, $result)
    {
        $data = self::$request_data[$shipping_key];
        $shipping_info = Shippings::getCarrierInfo(self::$module[$shipping_key]);

        if (!empty($data['url'])) {
            fn_log_event('requests', 'shipping', array(
                'url'      => $data['url'],
                'response' => $result,
                'data'     => var_export($data['data'], true),
                'shipping' => self::$module[$shipping_key],
            ));
        }

        $params = array(
            'period' => 'C',
            'time_from' => time() - SECONDS_IN_HOUR,
            'time_to' => time(),
            'q_type' => 'requests',
            'q_action' => 'shipping'
        );

        list($logs_data, $params) = fn_get_logs($params);

        $total_items = 0;
        foreach ($logs_data as $log) {
            $company_id = isset($log['company_id']) ? $log['company_id'] : 0;

            if (isset($log['content']['shipping']) && $log['content']['shipping'] == self::$module[$shipping_key]) {
                $total_items++;
            }
        }

        if ($total_items >= self::REQUEST_ERRORS_THRESHOLD) {
            /** @var \Tygh\Mailer\Mailer $mailer */
            $mailer = Tygh::$app['mailer'];
            $log_message = __('request_error_information', array('[shipping]' => $shipping_info['name']));

            $mailer->send(array(
                'to' => 'company_site_administrator',
                'from' => 'default_company_site_administrator',
                'data' => array(
                    'shipping' => self::$module[$shipping_key],
                    'log_message' => $log_message
                ),
                'template_code' => 'shipping_error',
                'tpl' => 'shipping/shipping_error.tpl', // this parameter is obsolete and is used for back compatibility
                'company_id' => $company_id,
            ), 'A', Registry::get('settings.Appearance.backend_default_language'));
        }
    }
}
