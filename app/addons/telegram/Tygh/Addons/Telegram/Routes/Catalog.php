<?php

namespace Tygh\Addons\Telegram\Routes;

class Catalog extends ARoute {
    public function render($params, $context) {
        if (1) {
            list($categories) = fn_get_categories($params);
            return [
                'message'=> 'Вот такие категории у нас для вас есть',
                'inline_keyboard' => [[
                    [
                        'text' => __('category'),
                        'callback_data' => '/catalog/1'
                    ],
                    [
                        'text' => __('category') . 2,
                        'callback_data' => '/catalog/2'
                    ]
                ]]
            ];
        }
    }
}
