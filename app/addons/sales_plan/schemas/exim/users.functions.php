<?php

function fn_exim_set_sales_plan($row, $user_id) {
    $update = array();
    if (isset($row['Sales plan']) && !empty($row['Sales plan'])) {
        $update['amount_plan'] = $row['Sales plan'];
    }
    if (isset($row['Frequency']) && !empty($row['Frequency'])) {
        $update['frequency'] = $row['Frequency'];
    }
    if (!empty($update)) {
        $update['user_id'] = $user_id;
        $update['company_id'] = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
        db_query('REPLACE INTO ?:sales_plan SET ?u', $update);
    }
}