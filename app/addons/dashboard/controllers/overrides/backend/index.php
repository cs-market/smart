<?php

use Tygh\Registry;
use Tygh\Tools\DateTimeHelper;
use Tygh\Enum\UserTypes;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/** @var array<string, int|string|array> $auth */
$auth = Tygh::$app['session']['auth'];

$runtime_company_id = Registry::get('runtime.company_id');
$storefront_id = empty($_REQUEST['storefront_id'])
    ? 0
    : (int) $_REQUEST['storefront_id'];

// Generate dashboard
if ($mode == 'index') {

    $time_periods = [
        DateTimeHelper::PERIOD_TODAY,
        DateTimeHelper::PERIOD_YESTERDAY,
        DateTimeHelper::PERIOD_THIS_MONTH,
        DateTimeHelper::PERIOD_LAST_MONTH,
        DateTimeHelper::PERIOD_THIS_YEAR,
        DateTimeHelper::PERIOD_LAST_YEAR,
    ];

    $time_period = DateTimeHelper::getPeriod(DateTimeHelper::PERIOD_THIS_MONTH);

    // Predefined period selected
    if (isset($_REQUEST['time_period']) && in_array($_REQUEST['time_period'], $time_periods)) {
        $time_period = DateTimeHelper::getPeriod($_REQUEST['time_period']);

        fn_set_session_data('dashboard_selected_period', serialize([
            'period' => $_REQUEST['time_period']
        ]));
    }
    // Custom period selected
    elseif (isset($_REQUEST['time_from'], $_REQUEST['time_to'])) {
        $time_period = DateTimeHelper::createCustomPeriod('@' . $_REQUEST['time_from'], '@' . $_REQUEST['time_to']);

        fn_set_session_data('dashboard_selected_period', serialize([
            'from' => $time_period['from']->format(DateTime::ISO8601),
            'to' => $time_period['to']->format(DateTime::ISO8601),
        ]));
    }
    $timestamp_from = $time_period['from']->getTimestamp();
    $timestamp_to = $time_period['to']->getTimestamp();

    $time_difference = $timestamp_to - $timestamp_from;

    $show_dashboard_preloader = true;
    if (defined('AJAX_REQUEST')) {
        /** @var \Tygh\Storefront\Repository $storefront_repository */
        $storefront_repository = Tygh::$app['storefront.repository'];
        /** @var \Tygh\Storefront\Storefront $storefront */
        $storefront = $storefront_repository->findById($storefront_id);

        $auth = Tygh::$app['session']['auth'];
        if ($auth['user_type'] === UserTypes::ADMIN) {
            $company_ids = $storefront ? $storefront->getCompanyIds() : [];
        } else {
            $company_ids = [$auth['company_id']];
        }

        $show_dashboard_preloader = false;

        $order_statuses = $orders = [];

        $params = [
            'period'        => 'C',
            'time_from'     => $timestamp_from,
            'time_to'       => $timestamp_to,
            'extra'         => [],
            'storefront_id' => $storefront_id,
            'get_conditions' => true,
            'status' => fn_get_settled_order_statuses(),
        ];
        list($fields, $join, $condition) = fn_get_orders($params, 0, false);

        $fields = [
            'sum(?:orders.total) as total',
            'avg(?:orders.total) as avg_total',
            'count(?:orders.order_id) as count_orders',
            "(SELECT COUNT(?:order_details.product_id) FROM ?:orders LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id WHERE 1 $condition) / count(?:orders.order_id) as avg_sku",
            'count(DISTINCT(?:orders.user_id)) as customers',
            "(SELECT SUM(x.count) FROM (SELECT COUNT(DISTINCT(cscart_order_details.product_id)) AS count FROM ?:orders LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id WHERE 1 $condition GROUP BY ?:orders.user_id) x) / (SELECT COUNT(DISTINCT(cscart_orders.user_id)) FROM ?:orders LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id WHERE 1 $condition) AS avg_sku_per_customer",
        ];

        $stats['current'] = db_get_row('SELECT ' . implode(', ', $fields) . " FROM ?:orders $join WHERE 1 $condition $group");
        
        $time_difference = $timestamp_to - $timestamp_from;
        $params['time_from'] = $timestamp_from - $time_difference;
        $params['time_to'] = $timestamp_to - $time_difference;

        list($tmp, $tmp, $condition) = fn_get_orders($params, 0, false);

        $fields = [
            'sum(?:orders.total) as total',
            'count(?:orders.order_id) as count_orders',
            'avg(?:orders.total) as avg_total',
            "(SELECT COUNT(?:order_details.product_id) FROM ?:orders LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id WHERE 1 $condition) / count(?:orders.order_id) as avg_sku",
            'count(DISTINCT(?:orders.user_id)) as customers',
            "(SELECT SUM(x.count) FROM (SELECT COUNT(DISTINCT(cscart_order_details.product_id)) AS count FROM ?:orders LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id WHERE 1 $condition GROUP BY ?:orders.user_id) x) / (SELECT COUNT(DISTINCT(cscart_orders.user_id)) FROM ?:orders LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id WHERE 1 $condition) AS avg_sku_per_customer",
        ];

        $stats['previous'] = db_get_row('SELECT ' . implode(', ', $fields) . " FROM ?:orders $join WHERE 1 $condition $group");
        arsort($stats);

        foreach (['total', 'count_orders', 'avg_total', 'avg_sku', 'customers', 'avg_sku_per_customer'] as $key) {
            $stats['diff'][$key] = $stats['current'][$key] - $stats['previous'][$key];
            $stats['diff'][$key.'_rel'] = fn_calculate_differences($stats['current'][$key], $stats['previous'][$key]);
        }

        Tygh::$app['view']->assign([
            'stats'             => $stats
        ]);
    }

    Tygh::$app['view']->assign([
        'time_from'                => $timestamp_from,
        'time_to'                  => $timestamp_to,
        'show_dashboard_preloader' => $show_dashboard_preloader,
        'storefront_id'            => $storefront_id,
    ]);
}

function fn_calculate_differences($new_value, $old_value)
{
    if ($old_value > 0) {
        $diff = ($new_value * 100) / $old_value;
        $diff = number_format($diff, 2);
    } else {
        $diff = '&infin;';
    }

    return $diff;
}
