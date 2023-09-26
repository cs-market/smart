<?php

namespace Tygh\Addons\Telegram\Routes;

use Tygh\Enum\YesNo;
use Tygh\Registry;

class Start extends ARoute {
    public function render($id, $params, $context) {
        if ($this->auth['area'] == 'A') {
            list($last_order) = fn_get_orders($params, 1);
            $last_order = reset($last_order);
            $commands = [[
                [
                    'text' => __('view_orders'),
                    'callback_data' => '/orders/?items_per_page=10'
                ],
                [
                    'text' => __('last_order'),
                    'callback_data' => '/orders/' . $last_order['order_id'],
                ],
            ]];
        } else {
            $commands = [];

            if (empty($this->auth['user_id'])) {
                $commands[] = [[
                    'text' => __('authorization'),
                    'callback_data' => '/auth'
                ]];
            }

//             $commands[] = [
//                 [
//                     'text' => __('search_products'),
//                     'callback_data' => '/search'
//                 ],
//                 [
//                     'text' => __('catalog'),
//                     'callback_data' => '/catalog'
//                 ],
//                 [
//                     'text' => __('products'),
//                     'callback_data' => '/products'
//                 ],
//             ];
            if ($this->auth['user_id'] && $order_id = db_get_field('SELECT max(order_id) FROM ?:orders WHERE user_id = ?i', $this->auth['user_id'])) {
                $commands[] = [
                    [
                        'text' => __('last_order_info'),
                        'callback_data' => '/orders/'.$order_id
                    ]
                ];
            }
        }

        return [
            'message'=> 'Здравствуйте, что будем делать?',
            'inline_keyboard' => $commands
        ];
    }

    public function privileges($id, $params, $context)
    {
        $privileges = [
            'frontend' => true,
            'backend' => true,
            'anonymous' => true,
        ];
        if (YesNo::toBool(Registry::get('addons.telegram.disable_anonymous_checkout'))) {
            $privileges['anonymous'] = false;
        }

        return $privileges;
    }
}
