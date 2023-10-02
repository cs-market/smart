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

function fn_extended_reward_points_exim_set_zero_amount_condition(&$conditions, &$joins) {
    $conditions[] = db_quote('reward_points.amount != 0');
    $joins = str_replace(' AND reward_points.usergroup_id = ', '', $joins);
}
