<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_extra_user_data() {
    return fn_get_user_additional_data(USER_EXTRA_DATA, Tygh::$app['session']['auth']['user_id']);
}
