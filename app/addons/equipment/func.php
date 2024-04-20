<?php

defined('BOOTSTRAP') or die('Access denied');

function fn_equipment_get_status_params_definition(&$status_params, &$type)
{
    if ($type == STATUS_MALFUNCTION) {
        $status_params = array (
            'code' => array (
                'type' => 'input',
                'label' => 'code',
            )
        );
    }

    return true;
}
