<?php

namespace Tygh\Api\Entities\v20;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Promotions extends AEntity {
    
    public function index($id = 0, $params = array())
    {
        $lang_code = $this->getLanguageCode($params);

        if ($id) {

            $data = fn_get_promotion_data($id, $lang_code);
            if ($data) {
                $status = Response::STATUS_OK;
            } else {
                $status = Response::STATUS_NOT_FOUND;
            }

        } else {

            list($data) = fn_get_promotions($params, null, $lang_code);
            $data = array_values($data);

            $status = Response::STATUS_OK;

        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        return $this->update(0, $params);
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        if (empty($params['external_id'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'external_id'
            ));

        } elseif (empty($params['zone'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'zone'
            ));

        } else {
            $lang_code = $this->getLanguageCode($params);
            $params['company_id'] = $this->getCompanyId($params);
            
            $this->gluePrimaryField($params);
            $this->buildConditions($params, $id, $arr);
            $this->buildBonuses($params);
            
            if ($promotion_id = fn_update_promotion($params, $id, $lang_code)) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'promotion_id' => $promotion_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_NOT_FOUND;
        if (!fn_check_company_id('promotions', 'promotion_id', $id)) {
            $status = Response::STATUS_FORBIDDEN;
        } else {
            fn_delete_promotions($id);
            $status = Response::STATUS_NO_CONTENT;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    protected function getCompanyId($params)
    {
        if ($this->isVendorUser()) {
            $company_id = $this->auth['company_id'];
        } elseif (!empty($params['company_id'])) {
            $company_id = $params['company_id'];
        } elseif (Registry::get('runtime.simple_ultimate')) {
            $company_id = Registry::get('runtime.forced_company_id');
        } else {
            $company_id = Registry::get('runtime.company_id');
        }

        return $company_id;
    }

    protected function gluePrimaryField(&$data) {
        if (!empty($data['suffix'])) {
            $data['external_id'] = trim($data['external_id']) . '.' . trim($data['suffix']);
            unset($data['suffix']);
        }
    }

    protected function buildConditions(&$object, $primary_object_id, &$processed_data) {
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
                    $products = $this->getValue('products', array_column($value, 'product_code'), $company_id);
                    // без amount просто имплодим, зону мы можем и не знать
                    foreach ($value as $key => &$data) {
                        if (isset($products[$data['product_code']])) {
                            $data['product_id'] = $products[$data['product_code']];
                            unset($data['product_code']);
                            if (empty($data['amount'])) unset($data['amount']);
                        } else {
                            unset($value[$key]);
                        }
                    }
                    if ($amount = array_column($value, 'amount')) {
                        $conditions_to_db['conditions'][] = [
                            'condition' => $condition,
                            'operator' => $operator,
                            'value' => $value
                        ];
                    } else {
                        $conditions_to_db['conditions'][] = [
                            'condition' => $condition,
                            'operator' => $operator,
                            'value' => implode(',', $products),
                        ];
                    }
                } elseif ($condition == 'users') {
                    if (!empty($users_value = $this->getValue($condition, $value, $company_id))) {
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
                    $usergroups = $this->getValue($condition, $value, $company_id);

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
                        'value' => $this->getValue('number', $value, $company_id)
                    ];
                }
            }

            $object['conditions'] = $conditions_to_db;
        }
    }

    protected function buildBonuses(&$object) {
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
                        'discount_value' => $this->getValue('number', $value),
                    ];
                } elseif ($bonus == 'discount_on_products') {
                    $value = array_filter(explode(',', $value));
                    array_walk($value, function(&$v) {list($t['product_code'], $t['discount']) = explode(':', $v);$v = $t;});
                    $value = array_filter($value, function($v) {return !empty($v['discount']);});
                    if (empty($value)) continue;
                    $value = fn_array_group($value, 'discount');

                    foreach ($value as $discount_value => $data) {
                        $products = $this->getValue('products', array_column($data, 'product_code'), $company_id);
                        if (!empty($products)) {
                            $bonuses_to_db[] = [
                                'bonus' => $bonus,
                                'discount_bonus' => $operator,
                                'value' => implode(',', $products),
                                'discount_value' => $this->getValue('number', $discount_value)
                            ];
                        }
                    }
                } elseif (in_array($bonus, ['free_products', 'promotion_step_free_products'])) {
                    $value = array_filter(explode(',', $value));
                    array_walk($value, function(&$v) {list($t['product_code'], $t['amount']) = explode(':', $v);$v = $t;});
                    $products = $this->getValue('products', array_column($value, 'product_code'), $company_id);
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
                    $value = reset($this->getValue('usergroup', $value, $company_id));
                    if (!empty($value)) {
                        $bonuses_to_db[] = [
                            'bonus' => $bonus,
                            'value' => $value,
                        ];
                    }
                } elseif (!empty(trim($value))) {
                    $bonuses_to_db[] = [
                        'bonus' => $bonus,
                        'value' => $this->getValue('number', $value, $company_id),
                    ];
                }
            }

            $object['bonuses'] = $bonuses_to_db;
        }
    }

    protected function getValue($type, $data, $company_id = 0) {
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
            return fn_maintenance_get_usergroup_ids($data);
        } elseif ($type == 'number') {
            return fn_maintenance_exim_import_price($data);
        }
    }

    public function privileges()
    {
        return array(
            'create' => 'manage_promotions',
            'update' => 'manage_promotions',
            'delete' => 'manage_promotions',
            'index'  => 'manage_promotions'
        );
    }
}
