<?php

use Tygh\Enum\NotificationSeverity;

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $repair_requests_repository = Tygh::$app['addons.equipment.repair_requests_repository'];

    if ($mode == 'add_repair_request') {
        $repair_data = $_REQUEST['request_data'];
        $result = $repair_requests_repository->save($repair_data, $_REQUEST['request_id'] ?? 0);
        if ($result->isFailure()) {
            fn_set_notification(NotificationSeverity::WARNING, __('warning'), $result->getFirstError());
        }
    }

    if ($mode == 'cancel_repair_request' && $_REQUEST['request_id']) {
        $repair_data = $repair_requests_repository->findById($_REQUEST['request_id']);
        if (!empty($repair_data['equipment'])) {
            $repair_data['status'] = __('equipment.repair_status_deleted');
            $result = $repair_requests_repository->save($repair_data, $_REQUEST['request_id'] ?? 0);
            if ($result->isFailure()) {
                fn_set_notification(NotificationSeverity::WARNING, __('warning'), $result->getFirstError());
            }
        }    
    }

    return [CONTROLLER_STATUS_OK, 'equipment.manage'];
}

$equipment_repository = Tygh::$app['addons.equipment.equipment_repository'];
$repairs_repository = Tygh::$app['addons.equipment.repair_requests_repository'];

if ($mode == 'manage') {
    if (!$auth['user_id']) return [CONTROLLER_STATUS_DENIED];

    $params = $_REQUEST;
    $repairs = [];
    list($equipment, $search) = $equipment_repository->find($params);
    if ($equipment) {
        $params['equipment_id'] = array_keys($equipment);
        list($repairs, ) = $repairs_repository->find($params, 0);
    }

    Tygh::$app['view']->assign([
        'equipment' => $equipment,
        'repairs' => $repairs,
        'search' => $search,
        'malfunction_types' => fn_equipment_get_malfunction_types(),
    ]);
} elseif (in_array($mode, ['add_repair_request', 'update_repair_request'])) {
    if (!$auth['user_id']) return [CONTROLLER_STATUS_DENIED];
    $params = $_REQUEST;

    $repair = ['malfunctions' => ['type' => false]];

    if (!empty($params['request_id'])) {
        $repair = $repairs_repository->findById($params['request_id']);
        $equipment = $repair['equipment'];
    } elseif (!empty($params['equipment_id'])) {
        $equipment = $equipment_repository->findById($params);
    }

    if (empty($equipment)) return [CONTROLLER_STATUS_DENIED];

    Tygh::$app['view']->assign(['repair' => $repair, 'equipment' => $equipment ]);

    Tygh::$app['view']->assign('malfunction_types', fn_equipment_get_malfunction_types());
}
