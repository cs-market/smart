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

function fn_promotion_import_glue_primary_field(&$import_data)
{
    foreach ($import_data as $key => &$data) {
        if (!empty($data['suffix'])) {
            $data['external_id'] = trim($data['external_id']) . '.' . trim($data['suffix']);
            unset($data['suffix']);
        }
    }

    return true;
}

function fn_promotion_import_put_optional_timestamp($timestamp)
{
    if (empty($timestamp)) {
        return 0;
    } else {
        return fn_parse_date($timestamp);
    }
}

function fn_promotion_import_build_conditions(&$object) {
    $company_id = Registry::ifget('runtime.company_id', $object['company_id']);
    $conditions = array_filter($object, function($key) {return (strpos($key, 'c.') === 0);}, ARRAY_FILTER_USE_KEY);
    $object = array_filter($object, function($key) {return (strpos($key, 'c.') !== 0);}, ARRAY_FILTER_USE_KEY);
    if (!empty($conditions)) {
        $conditions_to_db = [
            'set' => 'all',
            'set_value' => 1,
            'conditions' => []
        ];

        foreach ($conditions as $key => $value) {
            list(, $condition, $operator) = explode('.', $key);
            $conditions_to_db['conditions'][] = [
                'operator' => $operator,
                'condition' => $condition,
                'value' => fn_promotion_import_get_value($condition, $value, $company_id)
            ];
        }

        $object['conditions'] = serialize($conditions_to_db);
    }
}

function fn_promotion_import_build_bonuses(&$object) {
    $company_id = Registry::ifget('runtime.company_id', $object['company_id']);
    $bonuses = array_filter($object, function($key) {return (strpos($key, 'b.') === 0);}, ARRAY_FILTER_USE_KEY);
    $object = array_filter($object, function($key) {return (strpos($key, 'b.') !== 0);}, ARRAY_FILTER_USE_KEY);
    if (!empty($bonuses)) {
        $bonuses_to_db = [];
        foreach ($bonuses as $key => $value) {
            list(, $bonus, $operator) = explode('.', $key);
            if (is_numeric($value)) {
                $bonuses_to_db[] = [
                    'bonus' => $bonus,
                    'discount_bonus' => $operator,
                    'discount_value' => $value
                ];
            } else {
                $data = fn_promotion_import_get_value($bonus, $value, $company_id);
                $data = fn_array_group($data, 'amount');
                foreach ($data as $discount_value => $products) {
                    $bonuses_to_db[] = [
                        'bonus' => $bonus,
                        'discount_bonus' => $operator,
                        'value' => implode(',', array_column($products, 'product_id')),
                        'discount_value' => $discount_value
                    ];
                }
            }
        }
        $object['bonuses'] = serialize($bonuses_to_db);
    }
}

function fn_promotion_import_get_value($type, $data, $company_id) {
    if ($type == 'users') {
        $names = explode(',', $data);
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
    } elseif ($type == 'products' || $type == 'discount_on_products') {
        $data = explode(',', $data);
        array_walk($data, function(&$v) {list($t['product_code'], $t['amount']) = explode(':', $v);$v = $t;});

        $condition['product_code'] = db_quote(' AND product_code IN (?a)', array_column($data, 'product_code'));
        $condition['company_id'] = fn_get_company_condition('company_id', true, $company_id);
        $products = db_get_hash_single_array("SELECT product_id, product_code FROM ?:products WHERE 1 " . implode('', $condition), array('product_code', 'product_id'));

        if (!empty(array_filter(array_column($data, 'amount')))) {
            foreach ($data as $key => &$value) {
                if (isset($products[$value['product_code']])) {
                    $value['product_id'] = $products[$value['product_code']];
                    unset($value['product_code']);
                } else {
                    unset($data[$key]);
                }
            }
            return $data;
        } else {
            return implode(',', $products);
        }
    }
}

function fn_promotion_import_generate_promotion_hashes(&$object) {
    // TODO maybe use fn_category_promotion_update_promotion_post directly ?
    $conditions = unserialize($object['conditions']);
    $bonuses = unserialize($object['bonuses']);

    $object['conditions_hash'] = fn_promotion_serialize($conditions['conditions']);
    $object['users_conditions_hash'] = fn_promotion_serialize_users_conditions($conditions['conditions']);

    if (Registry::get('addons.category_promotion.status') == 'A') {
        if (isset($conditions['conditions'])) {
            fn_get_conditions($conditions['conditions'], $promo_extra);
        }

        $default_promo_extra = ['products' => '', 'usergroup' => ''];
        $promo_extra = array_map(function($arr) {return  implode(',', $arr);}, $promo_extra);
        $promo_extra = fn_array_merge($default_promo_extra, $promo_extra);

        $object['bonus_products'] = $promo_extra['products'];
        $object['usergroup'] = $promo_extra['usergroup'];
        $object['bonus_products'] = implode(',', fn_get_promotion_bonus_products($bonuses));
        $object['condition_categories'] = implode(',', fn_get_promotion_condition_categories($conditions));
    }
}
