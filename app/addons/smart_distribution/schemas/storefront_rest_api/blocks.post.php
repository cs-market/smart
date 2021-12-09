<?php

$schema['products']['content']['items']['post_function'] = static function ($products, $block_schema, $block, $params) {
    $currency = isset($params['currency'])
        ? $params['currency']
        : CART_PRIMARY_CURRENCY;
    $icon_sizes = isset($params['icon_sizes']['products'])
        ? $params['icon_sizes']['products']
        : $params['icon_sizes'];

    $products = fn_storefront_rest_api_format_products_prices($products, $currency);
    $products = fn_storefront_rest_api_set_products_icons($products, $icon_sizes);
    if (isset($params['get_features']) && $params['get_features']) {
        fn_gather_additional_products_data($products, [
            'get_features'        => true,
            'features_display_on' => 'A',
        ]);
    }

    return $products;
};

return $schema;
