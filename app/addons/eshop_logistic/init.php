<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_cities_pre',
    'update_city_post',
    'update_shipping_post',
    'calculate_rates_post',
    'calculate_cart_taxes_pre',
    'pickup_point_variable_init'
);

//fn_define("ESHOP_DEBUG", true);