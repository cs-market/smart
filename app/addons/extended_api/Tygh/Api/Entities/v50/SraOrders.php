<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\v40\SraOrders as BaseSraOrders;
use Tygh\Api\Response;

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
}
