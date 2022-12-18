<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'cron') {
    $u_data = db_get_array('SELECT user_id, company_id FROM ?:orders GROUP BY user_id, company_id HAVING count(order_id) >= ?i', Registry::get('addons.sales_plan.orders_amount'));
    foreach ($u_data as $key => &$data) {
        $add = db_get_row('SELECT avg(total) as amount_plan, (max(timestamp) - min(timestamp))/60/60/24/count(order_id) as frequency FROM (SELECT order_id, total, timestamp FROM ?:orders WHERE user_id = ?i AND company_id = ?i ORDER BY order_id DESC LIMIT 10) as o', $data['user_id'], $data['company_id']);
        if ($add['frequency'] < 0.1) {
            unset($u_data[$key]);
        } else {
            $data = array_merge($data, $add);
        }
    }
    db_query('REPLACE INTO ?:sales_plan ?m', $u_data);
    fn_print_die('Done');
}

if ($mode == 'daily_task') {
    $calendar_format = "d/m/Y";
    if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
        $calendar_format = "m/d/Y";
    }

    $params = array(
        'is_search' => 'Y',
        'time_from' => date($calendar_format),
        'time_to' => date($calendar_format),
        'only_data' => true
    );
    
    $notification_data = array();
    list($report, $params) = fn_generate_sales_report($params);
    $daily_report = array_shift($report);

    foreach ($daily_report as $company_id => $user_report) {
        $not_placed = array_filter($user_report, function($plan, $k) {
                return ($plan['fact'] == 0); 
            }, ARRAY_FILTER_USE_BOTH);
        // $less_placed = array_filter($user_report, function($plan, $k) {
        //      return (($plan['fact'] < $plan['plan']) &&  $plan['fact'] != 0) ; 
        //  }, ARRAY_FILTER_USE_BOTH);

        //company_notification
        $company_managers = db_get_fields("SELECT user_id FROM ?:users LEFT JOIN ?:companies ON ?:users.company_id = ?:companies.company_id WHERE user_type = ?s AND ?:users.company_id = ?i AND ?:companies.notify_manager_order_insufficient = 'Y'", 'V', $company_id);

        if ($not_placed) {
            foreach (array_keys($not_placed) as $user_id) {
                $not_placed[$user_id]['manager'] = db_get_field('SELECT u.email FROM ?:users AS u LEFT JOIN ?:user_managers AS um ON um.manager_id = u.user_id WHERE um.user_id = ?i AND vendor_manager IN (?a)', $user_id, $company_managers);
            }
            foreach ($not_placed as $user_id => $data) {
                if (!empty($data['manager'])) {
                    $notification_data[$data['manager']]['not_placed'][] = $user_id;
                }
            }
        }
        // if ($less_placed) {
        //  foreach (array_keys($less_placed) as $user_id) {
        //      $less_placed[$user_id]['manager'] = db_get_field('SELECT u.email FROM ?:users AS u LEFT JOIN ?:vendors_customers AS vc ON vc.vendor_manager = u.user_id WHERE vc.customer_id = ?i AND vendor_manager IN (?a)', $user_id, $company_managers);
        //  }
        //  foreach ($less_placed as $user_id => $data) {
        //      if (!empty($data['manager'])) {
        //          $notification_data[$data['manager']]['less_placed'][] = $user_id;
        //      }
        //  }
        // }
    }

    $mailer = Tygh::$app['mailer'];

    foreach ($notification_data as $manager_email => $data) {
        $mailer->send(array(
            'to' => $manager_email,
            'from' => 'default_company_orders_department',
            'data' => array('data' => $data),
            'tpl' => 'addons/sales_plan/sales_notification.tpl',
        ), 'A');
    }
}

if ($mode == 'view') {
    $params = $_REQUEST;
    $function = 'fn_generate_' . $params['type'];

    if (($params['type']) && (is_callable($function))) {
        $params['export'] = ($action == 'export');
        $search_schema = fn_get_schema('reports', $params['type']);
        Tygh::$app['view']->assign('search_schema', $search_schema);
        list($report, $params) = $function($params);
        if ($action == 'csv') {
            $export = fn_exim_put_csv($report, $params, '"');
            $url = fn_url("exim.get_file?filename=" . $params['filename'], 'A', 'current');
            return array(CONTROLLER_STATUS_OK, $url);
        } elseif ($action == 'export'){
            if (isset($search_schema[$dispatch_extra]['data_params'])) {
                $suffix = '';
                foreach ($search_schema[$dispatch_extra]['data_params'] as $param) {
                    if (!empty($params[$search_schema[$param]['name']]))
                    $suffix .= '&' . $search_schema[$param]['name'] . '=' . $params[$search_schema[$param]['name']];
                }
            }
            $url = fn_url($search_schema[$dispatch_extra]['data_url'] . implode(',', $report) . $suffix, 'A', 'current');
            return array(CONTROLLER_STATUS_OK, $url);
        } else {
            Tygh::$app['view']->assign('report', $report);
            Tygh::$app['view']->assign('search', $params);
        }
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
}
