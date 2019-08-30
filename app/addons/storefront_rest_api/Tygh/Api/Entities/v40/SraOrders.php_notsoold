<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Api\Entities\v40;

use Tygh\Api\Entities\Orders;

/**
 * Class SraOrders
 *
 * @package Tygh\Api\Entities
 */
class SraOrders extends Orders
{
    /** @inheritdoc */
    public function index($id = 0, $params = array())
    {
        $result = parent::index($id, $params);

        $currency = $this->safeGet($params, 'currency', CART_PRIMARY_CURRENCY);

        if ($id && !empty($result['data'])) {
            $result['data'] = fn_storefront_rest_api_format_order_prices($result['data'], $currency);
        } elseif (!empty($result['data']['orders'])) {
            foreach ($result['data']['orders'] as &$order) {
                $order = fn_storefront_rest_api_format_order_prices($order, $currency);
            }
            unset($order);
        }

        return $result;
    }
}