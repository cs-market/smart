<?php

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . '/maintenance/schemas/exim/usergroups.functions.php');

$schema = array(
    'section' => 'users',
    'pattern_id' => 'usergroups',
    'name' => __('usergroups'),
    'key' => array('usergroup_id'),
    'order' => 0,
    'table' => 'usergroup_descriptions',
    'import_only' => true,
    'permissions' => array(
        'import' => 'user_groups',
    ),
    'import_skip_db_processing' => true,
    'references' => array(
        'usergroups' => array(
            'reference_fields' => array('usergroup_id' => '&usergroup_id'),
            'multilang' => true,
            'join_type' => 'LEFT',
        ),
    ),
    'options' => array(
        'only_create' => array(
            'title' => 'maintenance.only_create',
            'type' => 'checkbox',
            'default_value' => 'Y',
        ),
    ),
    'export_fields' => array(
        'Usergroup' => array(
            'db_field' => 'usergroup',
            'required' => true,
            'alt_key' => true,
        ),
        'Status' => array(
            'db_field' => 'status',
            'table' => 'usergroups',
        ),
    ),
    'import_process_data' => array(
        'process_import' => array(
            'function' => 'fn_exim_import_usergroups',
            'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
            'import_only' => true,
        ),
    ),
);

return $schema;
