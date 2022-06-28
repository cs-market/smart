<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        if (isset($_REQUEST['file_promotion_data'])) {
            $file = fn_filter_uploaded_data('promotion_data');
            if (($data = fn_exim_get_csv(array(), $file['users_csv']['path'], array('validate_schema'=> false))) != false) {
                $column_name = key(reset($data));
                $users = array_column($data, $column_name);
                $company_condition = '';
                if ($company_id = $_REQUEST['promotion_data']['company_id']) {
                    $company_condition .= db_quote(' AND company_id = ?i', $company_id);
                }
                $user_ids = db_get_fields("SELECT user_id FROM ?:users WHERE user_login IN (?a) $company_condition", $users);
                if (!empty($user_ids)) {
                    $found = false;
                    foreach ($_POST['promotion_data']['conditions']['conditions'] as $i => &$group_item) {
                        if ($group_item['condition'] == 'users') {
                            $users = explode(',', $group_item['value']);
                            $group_item['value'] = implode(',', array_unique(array_merge($users, $user_ids)));
                            $found = true;
                        }
                        if ($group_item['condition'] == 'csv_users') {
                            $operator = $group_item['operator'];
                            unset($_POST['promotion_data']['conditions']['conditions'][$i]);
                        }
                    }
                    if (!$found) {
                        $_POST['promotion_data']['conditions']['conditions'][] = ['operator' => $operator, 'condition' => 'users', 'value' => $user_ids];
                    }
                }
            }
        }
    }
}
