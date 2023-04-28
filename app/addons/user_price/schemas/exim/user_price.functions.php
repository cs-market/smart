<?php

defined('BOOTSTRAP') or die('Access denied');

function fn_import_user_price(&$primary_object_id, &$object, &$options, &$processed_data, &$processing_groups) {
    static $db_users;
    if (!empty($primary_object_id)) {
        $name = trim($object['Name']);
        if (is_callable('fn_maintenance_get_usergroup_ids')) {
            $fn_load_usergroups = function ($name) {                
                return reset(fn_maintenance_get_usergroup_ids($name));
            };
        } else {
            $fn_load_usergroups = function ($name) use ($object) {
                return db_get_field('SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s AND lang_code = ?s', $name, $object['lang_code']);
            };
        }

        if (empty($db_users[$name]) && $ug_id = $fn_load_usergroups($name)) {
            $price = array(
                'product_id' => $primary_object_id['product_id'],
                'price' => $object['price'],
                'usergroup_id' => $ug_id,
            );
            if(db_query("REPLACE INTO ?:product_prices ?e", $price)) {
                $processed_data['E'] += 1;
            }
            //process_qty_discounts
        } else {
            $users = array();
            if (!isset($db_users[$name])) {
                $names = explode(',', $name);
                $search_fields = array('?:users.user_login', '?:users.firstname', '?:users.email');

                list($fields, $join, $condition) = fn_get_users(['get_conditions' => true], $_SESSION['auth']);

                foreach ($search_fields as $level => $field) {
                    $parts = array();
                    foreach ($names as $search) {
                        $parts[] = db_quote("$field = ?s", $search);
                    }

                    $expression[] = ' WHEN (' . implode(' OR ', $parts) . ') THEN ' . $level;
                    $conditions[] = implode(' OR ', $parts);
                }
                if (!empty($expression)) {
                    $case = ' CASE ' . implode(' ', $expression) . ' END AS level';
                    $search_fields[] = $case;
                    $condition['case_condition'] = ' AND ( ' . implode(' OR ', $conditions) . ' ) ';
                }

                $users = db_get_hash_multi_array("SELECT " . implode(', ', $search_fields) . " FROM ?:users WHERE 1" . implode('', $condition) , array('level'));

                if (!empty($users)) {
                    ksort($users);
                    $users = array_unique(array_column(reset($users), 'user_id'));
                }
                $db_users[$name] = $users;
            } else {
                $users = $db_users[$name];
            }

            if (!empty($users)) {
                $price = array();
                foreach ($users as $user) {
                    $price[] = array(
                        'user_id' => $user,
                        'price' => $object['price'],
                    );
                }

                fn_set_hook('user_price_exim_product_price_pre', $object, $price);

                if (fn_update_product_user_price($primary_object_id['product_id'], $price, false)) {
                    $processed_data['E'] += count($price);
                } else {
                    // skip record
                    $processed_data['S'] += count($price);
                }
            }
        }
    } else {
        // skip record
        $processed_data['S'] += 1;
    }
}
