<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;

function fn_promotion_import_glue_primary_field(&$import_data) {
    foreach ($import_data as $key => &$data) {
        if (!empty($data['suffix'])) {
            $data['external_id'] = trim($data['external_id']) . '.' . trim($data['suffix']);
            unset($data['suffix']);
        }    
    }

    return true;
}

function fn_promotion_import_fill_company_id(&$alt_keys, &$object) {
    if (fn_allowed_for('MULTIVENDOR')) {
        if ($cid = Registry::get('runtime.company_id')) {
            $object['company_id'] = $cid;
        }
        $alt_keys['company_id'] = $object['company_id'];
    }
}

function fn_promotion_import_put_optional_timestamp($timestamp, $end_time)
{
    if (empty($timestamp)) {
        return 0;
    } else {
        $timestamp = str_replace('.', '/', $timestamp);
        return fn_parse_date($timestamp, $end_time);
    }
}

function fn_promotion_import_build_conditions(&$object, $primary_object_id, &$processed_data) {
    $company_id = Registry::ifget('runtime.company_id', $object['company_id']);
    $conditions = array_filter($object, function($val, $key) {return (strpos($key, 'c.') === 0 && !empty($val));}, ARRAY_FILTER_USE_BOTH);
    $object = array_filter($object, function($key) {return (strpos($key, 'c.') !== 0);}, ARRAY_FILTER_USE_KEY);
    if (!empty($conditions)) {
        $conditions_to_db = [
            'set' => 'all',
            'set_value' => 1,
            'conditions' => []
        ];

        foreach ($conditions as $key => $value) {
            list(, $condition, $operator) = explode('.', $key);
            if ($condition == 'products') {
                $value = array_filter(explode(',', $value));
                array_walk($value, function(&$v) {list($t['product_code'], $t['amount']) = explode(':', $v);$v = $t;});
                $products = fn_promotion_import_get_value('products', array_column($value, 'product_code'), $company_id);
                // без amount просто имплодим, зону мы можем и не знать
                foreach ($value as &$data) {
                    $data['product_id'] = $products[$data['product_code']];
                    unset($data['product_code']);
                    if (empty($data['amount'])) unset($data['amount']);
                }
                if ($amount = array_column($value, 'amount')) {
                    $conditions_to_db['conditions'][] = [
                        'condition' => $condition,
                        'operator' => $operator,
                        'value' => fn_promotion_import_get_value('number', $value, $company_id)
                    ];
                } else {
                    $conditions_to_db['conditions'][] = [
                        'condition' => $condition,
                        'operator' => $operator,
                        'value' => implode(',', $products),
                    ];
                }
            } elseif ($condition == 'users') {
                if (!empty($users_value = fn_promotion_import_get_value($condition, $value, $company_id))) {
                    $conditions_to_db['conditions'][] = [
                        'condition' => $condition,
                        'operator' => $operator,
                        'value' => $users_value
                    ];
                } elseif (!empty($value)) {
                    $object['status'] = 'D';
                    $processed_data['disabled_promo_notification'][] = [
                        'promotion_id' => $primary_object_id['promotion_id'],
                        'external_id' => $object['external_id'],
                        'object' => $condition,
                        'value' => $value,
                    ];
                }
            } elseif ($condition == 'usergroup') {
                $usergroups = fn_promotion_import_get_value($condition, $value, $company_id);

                if ($usergroups) {
                    foreach ($usergroups as $ug_id) {
                        $usergroup_conditios[] = [
                            'condition' => $condition,
                            'operator' => $operator,
                            'value' => $ug_id
                        ];
                    }
                    $conditions_to_db['conditions'][] = [
                        'set' => 'any',
                        'set_value' => 1,
                        'conditions' => $usergroup_conditios
                    ];
                    unset($usergroup_conditios);
                } elseif (!empty($value)) {
                    $object['status'] = 'D';
                    $processed_data['disabled_promo_notification'][] = [
                        'promotion_id' => $primary_object_id['promotion_id'],
                        'external_id' => $object['external_id'],
                        'object' => $condition,
                        'value' => $value,
                    ];
                }
            } else {
                $conditions_to_db['conditions'][] = [
                    'condition' => $condition,
                    'operator' => $operator,
                    'value' => fn_promotion_import_get_value('number', $value, $company_id)
                ];
            }
        }

        $object['conditions'] = serialize($conditions_to_db);
    }
}

function fn_promotion_import_build_bonuses(&$object) {
    $company_id = Registry::ifget('runtime.company_id', $object['company_id']);
    $bonuses = array_filter($object, function($val, $key) {return (strpos($key, 'b.') === 0 && !empty($val));}, ARRAY_FILTER_USE_BOTH);
    $object = array_filter($object, function($key) {return (strpos($key, 'b.') !== 0);}, ARRAY_FILTER_USE_KEY);
    if (!empty($bonuses)) {
        $bonuses_to_db = [];
        foreach ($bonuses as $key => $value) {
            list(, $bonus, $operator) = explode('.', $key);
            if (in_array($bonus, ['product_discount', 'order_discount'])) {
                $bonuses_to_db[] = [
                    'bonus' => $bonus,
                    'discount_bonus' => $operator,
                    'discount_value' => fn_promotion_import_get_value('number', $value),
                ];
            } elseif ($bonus == 'discount_on_products') {
                $value = array_filter(explode(',', $value));
                array_walk($value, function(&$v) {list($t['product_code'], $t['discount']) = explode(':', $v);$v = $t;});
                $value = array_filter($value, function($v) {return !empty($v['discount']);});
                if (empty($value)) continue;
                $value = fn_array_group($value, 'discount');

                foreach ($value as $discount_value => $data) {
                    $products = fn_promotion_import_get_value('products', array_column($data, 'product_code'), $company_id);
                    if (!empty($products)) {
                        $bonuses_to_db[] = [
                            'bonus' => $bonus,
                            'discount_bonus' => $operator,
                            'value' => implode(',', $products),
                            'discount_value' => fn_promotion_import_get_value('number', $discount_value)
                        ];
                    }
                }
            } elseif (in_array($bonus, ['free_products', 'promotion_step_free_products'])) {
                $value = array_filter(explode(',', $value));
                array_walk($value, function(&$v) {list($t['product_code'], $t['amount']) = explode(':', $v);$v = $t;});
                $products = fn_promotion_import_get_value('products', array_column($value, 'product_code'), $company_id);
                // без amount просто имплодим, зону мы можем и не знать
                foreach ($value as $key => &$data) {
                    $data['product_id'] = $products[$data['product_code']];
                    unset($data['product_code']);
                    if (empty($data['amount'])) $data['amount'] = 1;
                    if (empty($data['product_id'])) unset($value[$key]);
                }
                $bonuses_to_db[] = [
                    'bonus' => $bonus,
                    'value' => $value,
                ];
            } elseif ($bonus == 'give_usergroup') {
                $value = reset(fn_promotion_import_get_value('usergroup', $value, $company_id));
                if (!empty($value)) {
                    $bonuses_to_db[] = [
                        'bonus' => $bonus,
                        'value' => $value,
                    ];
                }
            } elseif (!empty(trim($value))) {
                $bonuses_to_db[] = [
                    'bonus' => $bonus,
                    'value' => fn_promotion_import_get_value('number', $value, $company_id),
                ];
            }
        }

        $object['bonuses'] = serialize($bonuses_to_db);
    }
}

function fn_promotion_import_get_value($type, $data, $company_id = 0) {
    if ($type == 'users') {
        $names = explode(',', $data);
        $names = array_filter($names);
        $search_fields = array('cscart_users.user_login', 'cscart_users.firstname', 'cscart_users.email');

        list($fields, $join, $condition) = fn_get_users(['get_conditions' => true, 'company_ids' => [$company_id]], $_SESSION['auth']);
        $condition['company_id'] = fn_get_company_condition('?:users.company_id', true, $company_id);

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
            $fields[] = $case;
            $condition['case_condition'] = ' AND ( ' . implode(' OR ', $conditions) . ' ) ';
        }

        $users = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:users $join WHERE 1" . implode('', $condition));
        return implode(',', array_column($users, 'user_id'));
    } elseif ($type == 'products') {
        if (!is_array($data)) {
            $data = explode(',', $data);
        }
        $data = array_filter($data);
        $condition = [
            'product_code' => db_quote(' AND product_code IN (?a)', $data),
            'company_id' => fn_get_company_condition('company_id', true, $company_id),
        ];
        return db_get_hash_single_array("SELECT product_id, product_code FROM ?:products WHERE 1 " . implode('', $condition), array('product_code', 'product_id'));
    } elseif ($type == 'usergroup') {
        return fn_exim_smart_distribution_get_usergroup_ids($data);
    } elseif ($type == 'number') {
        return fn_smart_distribution_exim_import_price($data);
    }
}

function fn_promotion_import_generate_promotion_hashes(&$object) {
    if (Registry::get('addons.category_promotion.status') == 'A') {
        if (!empty($object['conditions'])) {
            $conditions = unserialize($object['conditions']);
            $object['conditions_hash'] = fn_promotion_serialize($conditions['conditions']);
            $object['users_conditions_hash'] = fn_promotion_serialize_users_conditions($conditions['conditions']);
            if (isset($conditions['conditions'])) {
                fn_get_conditions($conditions['conditions'], $promo_extra);
            }

            $default_promo_extra = ['products' => '', 'usergroup' => ''];
            $promo_extra = array_map(function($arr) {return  implode(',', $arr);}, $promo_extra);
            $promo_extra = fn_array_merge($default_promo_extra, $promo_extra);

            $object['products'] = $promo_extra['products'];
            $object['usergroup'] = $promo_extra['usergroup'];
            $object['condition_categories'] = implode(',', fn_get_promotion_condition_categories($conditions));
        }

        if (!empty($object['bonuses'])) {
            $bonuses = unserialize($object['bonuses']);
            $object['bonus_products'] = implode(',', fn_get_promotion_bonus_products($bonuses));
        }
    }
}

function fn_promotion_import_disabled_promo_notification(&$processed_data) {
    if (!empty($processed_data['disabled_promo_notification'])) {
        $promotion_details = array_map(function($data) {
            $prefix = $body = $suffix = '';
            if (!empty($data['promotion_id'])) {
                $prefix = '<a href="' . fn_url('promotions.update?promotion_id='.$data['promotion_id']) . '">';
                $suffix = '</a> ';
            }
            $body = $data['external_id'];
            $suffix .= $data['object'] . '=>' . $data['value'];
            return $prefix.$body.$suffix;
        }, $processed_data['disabled_promo_notification']);

        if (!empty($promotion_details)) {
            fn_set_notification('W', __('warning'), __('promotion_import.disabled_promo_notification') . ':<br>' . implode('<br>', $promotion_details));
        }
    }
}
