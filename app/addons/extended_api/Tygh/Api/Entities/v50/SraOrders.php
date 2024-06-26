<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\v40\SraOrders as BaseSraOrders;
use Tygh\Api\Response;
use Tygh\Registry;

/**
 * Class SraOrders
 *
 * @package Tygh\Api\Entities
 */
class SraOrders extends BaseSraOrders
{
    public function privilegesCustomer()
    {
        $privileges = parent::privilegesCustomer();
        $privileges['delete'] = $this->auth['is_token_auth'];

        return $privileges;
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_NOT_FOUND;

        fn_set_hook('api_delete_order', $id, $status, $data);

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params) {
        if ($user_data = $this->safeGet($params, 'user_data', array())) {
            $params['profile_id'] = $this->safeGet($params, 'profile_id', null);

            $current_user_data = fn_get_user_info($this->auth['user_id'], true, $params['profile_id']);
            $user_data = fn_array_merge(
                $current_user_data,
                $user_data
            );

            fn_update_user($this->auth['user_id'], $user_data, $this->auth, true, []);
        }

        $points_to_use = $this->safeGet($params, 'points_to_use', false);
        if (!empty($points_to_use)) {
            Registry::set('runtime.controller', 'checkout');
            Registry::set('runtime.mode', 'update');
            $params['points_info']['in_use']['points'] = $points_to_use;
        }
        return parent::create($params);
    }
}
