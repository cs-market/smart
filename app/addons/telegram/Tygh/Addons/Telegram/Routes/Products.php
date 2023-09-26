<?php

namespace Tygh\Addons\Telegram\Routes;

class Products extends ARoute {
    private $separate_products = false;

    public function render($params, $context) {

        if ($params['id']) {
            $product = fn_get_product_data($params['id'], $_SESSION['auth']);
            return [
                'message' => $product['product'] . ' $' . fn_format_price($product['price']),
                'photo' => $product['main_pair']['detailed']['https_image_path'],
                'inline_keyboard' => [[[
                    'text' => __('add_to_cart'),
                    'callback_data' => '/cart/'.$product['product_id'],
                ]]],
            ];
        } else {
            list($products, $search) = fn_get_products($params['params'], 6);

            if ($separate_products) {
                $inline_keyboard = [];
            } else {
                $s_products = array_chunk($products, 2, true);
                $inline_keyboard = [];
                foreach ($s_products as $row => $products) {
                    foreach ($products as $product_id => $product) {
                        $inline_keyboard[$row][] = [
                            'text' => $product['product'],
                            'callback_data' => '/products/'.$product_id,
                        ];
                    }
                }
            }

            $inline_keyboard[] = $this::generatePagination($search, '/orders');

            return [
                'message'=> 'Всего товаров: ' . $search['total_items'] . '. Страница ' . $search['page'],
                'inline_keyboard' => $inline_keyboard,
            ];
        }
    }
}
