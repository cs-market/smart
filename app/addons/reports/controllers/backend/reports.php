<?php

use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'view') {
    $params = $_REQUEST;

    if (($params['type'])) {
        $params['export'] = ($action == 'export');
        $reports = fn_get_schema('reports', 'schema');
        if (isset($reports[$params['type']])) {
            $report = $reports[$params['type']];

            if (isset($report['allowed_for']) && !fn_allowed_for($report['allowed_for'])) return ([CONTROLLER_STATUS_NO_PAGE]);
            if (isset($report['condition']) && !$report['condition']) return ([CONTROLLER_STATUS_NO_PAGE]);

            $controls = $reports[$params['type']]['controls'];
            $function = $report['function'] ?? 'fn_generate_' . $params['type'];
            if (is_file($report['include'])) include_once($report['include']);

            Tygh::$app['view']->assign('controls', $controls);
            if (is_callable($function)) {
                list($report, $params) = $function($params);
                if ($action == 'csv') {
                    $export = fn_exim_put_csv($report, $params, '"');
                    $url = fn_url("exim.get_file?filename=" . $params['filename'], 'A', 'current');
                    return array(CONTROLLER_STATUS_OK, $url);
                } elseif ($action == 'export'){
                    if (isset($controls[$dispatch_extra]['data_params'])) {
                        $suffix = '';
                        foreach ($controls[$dispatch_extra]['data_params'] as $param) {
                            if (!empty($params[$controls[$param]['name']]))
                            $suffix .= '&' . $controls[$param]['name'] . '=' . $params[$controls[$param]['name']];
                        }
                    }
                    $url = fn_url($controls[$dispatch_extra]['data_url'] . implode(',', $report) . $suffix, 'A', 'current');
                    return array(CONTROLLER_STATUS_OK, $url);
                } else {
                    Tygh::$app['view']->assign('report', $report);
                    Tygh::$app['view']->assign('search', $params);
                }
            }
        }
    } else {
        return array(CONTROLLER_STATUS_REDIRECT, $_SERVER['HTTP_REFERER']);
    }
}
