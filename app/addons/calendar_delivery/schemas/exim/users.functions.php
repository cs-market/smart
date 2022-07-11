<?php

/**
 * exim from string days to one day
 *
 * @param integer $day_number [0..6] day number
 * @param integer $day_number [0..6] day number
 * @return string Y|N
 **/
function fn_exim_get_delivery_date($user_id, $day_number)
{
    $days_string = fn_get_customer_delivery_dates($user_id);

    return (string) $days_string[$day_number] ? 'Y' : 'N';
}

/**
 * exim put selected day to str days
 *
 * @param int $user_id User ID
 * @param mixed $days_value true: {Y|+|'true'|1}
 * @param integer $day_number [0..6] day number
 * @return void
 **/
function fn_exim_set_delivery_date($primary_object_id, &$object)
{
    $user_id = reset($primary_object_id);

    $allow_words = [
        'y', 'Y', 'true', '1', 'da', 'Da'
    ];

    if ($user_id) {
        $prev_value = fn_get_customer_delivery_dates($user_id);
    } else {
        $prev_value = 1111111;
    }
    $delivery_date = $prev_value;

    foreach (range(0, 6) as $day) {
        if (isset($object['delivery_date_'.$day])) {
            $delivery_date[$day] = in_array($object['delivery_date_'.$day], $allow_words) ? '1' : '0';
            unset($object['delivery_date_'.$day]);
        }
    }

    if ($delivery_date != $prev_value && $delivery_date != '0000000') {
        db_query('UPDATE ?:users SET ?u WHERE user_id = ?i', ['delivery_date' => $delivery_date], $user_id);
    }

    return false;
}

/**
 * exim get shipping day in string
 *
 * @param string $days_str list of day
 * @return string string with sunday as past elm
 **/
function fn_exim_get_delivery_date_line(string $days_string)
{
    $first_char = $days_string[0];
    $days_string = substr($days_string, 1) . $first_char;

    return $days_string;
}

/**
 * exim put shipping day in string
 *
 * @param string $days_string true: {Y|+|'true'|1}
 * @param int $user_id User ID
 * @return void
 **/
function fn_exim_set_delivery_date_line($days_string)
{
    $last_char = $days_string[strlen($days_string) - 1];
    $days_string = $last_char . substr($days_string, 0, -1);

    return $days_string;
}
