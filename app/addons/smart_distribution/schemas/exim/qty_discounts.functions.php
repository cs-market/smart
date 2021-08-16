<?php

use Tygh\Registry;
use Tygh\Languages\Languages;

function fn_exim_mve_put_usergroup($usergroup, $lang_code) {
    $default_usergroups = fn_get_default_usergroups($lang_code);
    foreach ($default_usergroups as $usergroup_id => $ug) {
        if ($ug['usergroup'] == $usergroup) {
            return $usergroup_id;
        }
    }

    $usergroup_id = fn_get_usergroup_id($usergroup, $lang_code);
    // Create new usergroup
    if (empty($usergroup_id) && !Registry::get('runtime.company_id')) {
        $_data = array(
            'type' => 'C', //customer
            'status' => 'A'
        );

        $usergroup_id = db_query("INSERT INTO ?:usergroups ?e", $_data);

        $_data = array(
            'usergroup_id' => $usergroup_id,
            'usergroup' => $usergroup,
        );

        foreach (Languages::getAll() as $_data['lang_code'] => $v) {
            db_query("INSERT INTO ?:usergroup_descriptions ?e", $_data);
        }
    }

    return $usergroup_id ? $usergroup_id : false;
}

function fn_exim_mve_check_usergroup($row, &$processed_data, &$skip_record) {
    if ($row['usergroup_id'] === false) {
        $skip_record = true;
        $processed_data['S']++;
    }
}