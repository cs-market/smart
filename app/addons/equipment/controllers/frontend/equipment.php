<?php

$equipment_repository = Tygh::$app['addons.equipment.repository'];

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'manage') {
    if (!$auth['user_id']) return [CONTROLLER_STATUS_DENIED];

    $params = $_REQUEST;
    $params['get_repairs'] = true;
    list($equipment, $search) = $equipment_repository->find($params);

    Tygh::$app['view']->assign('equipment', $equipment);
    Tygh::$app['view']->assign('search', $search);
    Tygh::$app['view']->assign('malfunction_types', fn_get_statuses(STATUS_MALFUNCTION));
}

if ($mode == 'add_repair_request') {
    Tygh::$app['view']->assign('malfunction_types', fn_get_statuses(STATUS_MALFUNCTION));
}
