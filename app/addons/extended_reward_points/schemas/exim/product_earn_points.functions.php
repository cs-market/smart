<?php

use Tygh\Enum\YesNo;

function fn_extended_reward_points_exim_cleanup_reward_points($object_id, $cleanup) {
    if (!YesNo::toBool($cleanup)) return;

    static $cleaned_products = array();
    foreach ($object_id as $_id) {
        if (empty($cleaned_products[$_id])) {
            db_query('DELETE FROM ?:reward_points WHERE object_id = ?i AND object_type = ?s', $_id, 'P');
            $cleaned_products[$_id] = true;
        }
    }
}
