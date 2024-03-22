<?php

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add') {
        fn_delete_notification_by_message(__('product_added_to_wl'));
    }
}
