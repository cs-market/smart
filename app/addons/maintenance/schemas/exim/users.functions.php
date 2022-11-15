<?php

function fn_maintenance_exim_set_usergroups($user_id, $data, $cleanup = true) {
    if ($cleanup) db_query("DELETE FROM ?:usergroup_links WHERE user_id = ?i", $user_id);
    if (!empty($data)) {
        $usergroups = fn_maintenance_exim_get_usergroup_ids($data, false);
        foreach ($usergroups as $ug_id => $status) {
            $_data = array(
                'user_id' => $user_id,
                'usergroup_id' => $ug_id,
                'status' => $status
            );
            db_query('REPLACE INTO ?:usergroup_links ?e', $_data);
        }
    }

    return true;
}
