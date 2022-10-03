<?php

use Tygh\Enum\SiteArea;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_managers_user_init($auth, &$user_info) {
    if (SiteArea::isStorefront(AREA)) {
        $user_info['managers'] = fn_smart_distribution_get_managers(array('user_id' => $auth['user_id']));
    }
}

