<?php

use Tygh\Registry;

$current_url = urlencode(Registry::get('config.current_url'));

$schema['top']['administration']['items']['logs']['subitems']['eshop_logistic.logs'] = [
    'href' => 'eshop_logistic.logs',
    'title' => __('eshop_logistic.logs')
];

$schema['top']['administration']['items']['storage']['subitems']['eshop_logistic.eshop_clear_cache'] = [
    'position' => 800,
    'href' => 'eshop_logistic.clear_cache?redirect_url=' . $current_url,
    'title' => __('eshop_logistic.eshop_clear_cache')
];
return $schema;