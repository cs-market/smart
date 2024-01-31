<?php

use Tygh\Enum\YesNo;

function fn_exim_import_usergroups(&$primary_object_id, &$object, $pattern, $options, &$processed_data, $processing_groups, &$skip_record) {
    if (empty($primary_object_id['usergroup_id']) || !YesNo::toBool($options['only_create'])) {
        $default_params = [
            'status' => 'A',
            'type' => 'C'
        ];
        $object = array_merge($default_params, $object);
        if (empty($primary_object_id['usergroup_id'])) {
            $processed_data['N']++;
        } else {
            $processed_data['E']++;
        }
        $primary_object_id['usergroup_id'] = fn_update_usergroup($object, $primary_object_id['usergroup_id'] ?? 0);
    } else {
        $processed_data['S']++;
        $skip_record = true;

        return false;
    }
}
