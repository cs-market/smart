<?php

$equipment_repository = Tygh::$app['addons.equipment.equipment_repository'];


defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';
    if ($mode == 'add_repair_request') {
        $repair_requests_repository = Tygh::$app['addons.equipment.repair_requests_repository'];
        $request_data = $_REQUEST['request_data'];
        $result = $repair_requests_repository->save($request_data);
        if ($result->isSuccess()) {
            $suffix = '&equipment_id=' . $request_data['equipment_id'];
        }
        return [CONTROLLER_STATUS_OK, 'equipment.manage' . $suffix];
    }

    return;
}

if ($mode == 'manage') {
    if (!$auth['user_id']) return [CONTROLLER_STATUS_DENIED];

    $params = $_REQUEST;
    $params['get_repairs'] = true;
    list($equipment, $search) = $equipment_repository->find($params);

    Tygh::$app['view']->assign('equipment', $equipment);
    Tygh::$app['view']->assign('search', $search);

    Tygh::$app['view']->assign('malfunction_types', fn_equipment_get_malfunction_types());
}

if ($mode == 'add_repair_request') {
    $params = $_REQUEST;

    if (!empty($params['equipment_id'])) {
        $equipment = $equipment_repository->findById($params);
        Tygh::$app['view']->assign('equipment', $equipment);
    }

    Tygh::$app['view']->assign('malfunction_types', fn_equipment_get_malfunction_types());
}
