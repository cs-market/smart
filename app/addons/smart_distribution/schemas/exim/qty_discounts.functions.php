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

    list($usergroup_id) = fn_exim_smart_distribution_get_usergroup_ids($usergroup);

    return $usergroup_id ? $usergroup_id : false;
}

function fn_exim_mve_check_usergroup($row, &$processed_data, &$skip_record) {
    if ($row['usergroup_id'] === false) {
        $skip_record = true;
        $processed_data['S']++;
    }
}