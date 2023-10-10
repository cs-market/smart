<?php

namespace Tygh\Addons\Telegram\Routes;

use Tygh\Registry;
use Tygh\Enum\SiteArea;

class Orders extends ARoute {
    public function render($id, $params, $context) {
        if (!empty($id)) {
            if ($order = fn_get_order_info($id)) {
                $return = [];
                $return['message'] = $this->getTelegramOrderInfo($order);

                if (in_array($this->area, ['A', 'V'])) {
                    $return['inline_keyboard'] = [
                        [[
                            'text' => __('link'),
                            'url' => fn_url('orders.details&order_id='.$order['order_id'], $this->area),
                        ]],                 
                        [[
                            'text' => __('telegram.change_order_status'),
                            'callback_data' => '/order_status/'.$id,
                        ]]
                    ];
                }
                return $return;
            } else {
                return [
                    'message' => __('order_not_found')
                ];
            }
        } else {
            list($orders, $search) = fn_get_orders($params, 6);

            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $buttons[] = [
                        'text' => '#' . $order['order_id'],
                        'callback_data' => '/orders/'.$order['order_id'],
                    ];
                }

                $inline_keyboard = array_chunk($buttons, 2);
                $inline_keyboard[] = $this::generatePagination($search, '/orders');
            }

            return [
                'message'=> 'Всего заказов: ' . $search['total_items'] . '. ' . __('page') . ' ' . $search['page'],
                'inline_keyboard' => $inline_keyboard,
            ];
        }
    }

    private function getTelegramOrderInfo($order) {
        $formatter = \Tygh::$app['formatter'];
        $info = [];
        $nl = "\r\n";

        // summary
        $info['order'] = __('order') . ': <a href="' . fn_url('orders.details&order_id='.$order['order_id'], $this->area) . '">#' . $order['order_id'] . '</a>';
        $info['order_date'] = __('order_date') . ': ' . fn_date_format($order['timestamp'], Registry::get('settings.Appearance.date_format'));
        $info['order_status'] = __('order_status') . ': ' . fn_get_status_data($order['status'], STATUSES_ORDER)['description'];

        $currencies = Registry::get('currencies');
        $currency_symbol = $currencies[CART_PRIMARY_CURRENCY]['symbol'];
        if (!empty((float) $order['shipping_cost']))
            $info['shipping_cost'] = __('shipping_cost') . ': ' . $formatter->asPrice($order['shipping_cost']);
        if (!empty((float) $order['discount']))
            $info['discount'] = __('discount') . ': ' . $formatter->asPrice($order['discount']);
        if (fn_allowed_for('MULTIVENDOR') && !empty($order['company_id']))
            $info['vendor'] = __('vendor') . ': ' . fn_get_company_name($order['company_id']);
        $info['total'] = __('total') . ': <b>' . $formatter->asPrice($order['total']) . '</b>';
        if (!empty($order['shipping'])) {
            $info['shipping_method'] = __('shipping_method') . ': ' . reset($order['shipping'])['shipping'];
        }
        if (!empty($order['payment_method']['payment'])) $info['payment_method'] = __('payment_method') . ': ' . $order['payment_method']['payment'];
        $info['end_summary'] = '';

        // customer info
        $info['name'] = __('name') . ': ' . $order['firstname'] . ' ' . $order['lastname'];
        $email = $this->getOrderParam($order, 'email');
        if (!empty($email))
            $info['email'] = __('email') . ': ' . $email;
        $login = $this->getOrderParam($order, 'user_login');
        if (!empty($login))
            $info['login'] = __('user_login') . ': ' . $login;
        $state = $this->getOrderParam($order, 'state');
        if (!empty($state))
        //     $info['state'] = __('state') . ': ' . fn_get_state_name($state, $this->getOrderParam($order, 'country'));
        // $city = $this->getOrderParam($order, 'city');
        // if (!empty($city))
        //     $info['city'] = __('city') . ': ' . $city;
        // $address = $this->getOrderParam($order, 'address');
        if (!empty($address))
            $info['address'] = __('address') . ': ' . $address;
        $phone = $this->getOrderParam($order, 'phone');
        if (!empty($phone))
            $info['phone'] = __('phone') . ': ' . $phone;
        $info['end_customer'] = '';

        // products table
        // $info['ordered_products'] = '<b>' . __('ordered_products') . '</b>';
        // foreach($order['products'] as $id => $product) {
        //     $info["ordered_products_name.$id"] = $product['product'];
        //     $info["ordered_products_code.$id"] = __('product_code') . ': ' . $product['product_code'];
        //     $info["ordered_products_data.$id"] = __('amount') . ': ' . $product['amount'] . ' * ' . $formatter->asPrice($product['price']);
        //     $info["ordered_products_nl.$id"] = '';
        // }

        return str_replace('&nbsp;', ' ', implode($nl, $info));
    }

    private function getOrderParam($order, $param) {
        foreach([$param, "s_$param", "b_$param"] as $p) {
            if (!empty($order[$p])) return $order[$p];
        }
        return false;
    }

    public function privileges($id, $params, $context)
    {
        return [
            'frontend' => true,
            'backend' => true,
            'anonymous' => false,
        ];
    }
}
