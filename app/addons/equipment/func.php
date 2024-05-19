<?php

defined('BOOTSTRAP') or die('Access denied');

function fn_equipment_get_status_params_definition(&$status_params, &$type)
{
    if ($type == STATUS_MALFUNCTION) {
        $status_params = array (
            'code' => array (
                'type' => 'input',
                'label' => 'code',
            ),
            'repair_tooltip' => array (
                'type' => 'textarea',
                'label' => 'equipment.repair_tooltip',
            )
        );
    }

    return true;
}

function fn_equipment_get_malfunction_types() {
    $statuses = fn_get_statuses(STATUS_MALFUNCTION);
    $malfunctions = [];
    foreach($statuses as $status) {
        $malfunctions[$status['params']['code']] = [
            'description' => $status['description'],
            'code' => $status['params']['code'],
            'repair_tooltip' => $status['params']['repair_tooltip']
        ];
    }
    return $malfunctions;
}
