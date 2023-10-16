<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\Orders as BaseOrders;
use Tygh\Api\Response;
use Tygh\Registry;

/**
 * Class Orders
 *
 * @package Tygh\Api\Entities\v50
 */
class Orders extends BaseOrders
{
    public function update($id, $params) {
        $columns = fn_get_table_fields('orders', ['order_id', 'company_id', 'status']);

        if ($direct_update = array_intersect_key($params, array_flip($columns))) {
            db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $direct_update, $id);
            $params = array_diff_key($params, $direct_update);
        }
        
        return parent::update($id, $params);
    }
}
