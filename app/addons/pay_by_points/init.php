<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
  'pre_add_to_cart',
  'check_add_to_cart_post',
  'add_product_to_cart_get_price',
  'add_to_cart',
  'get_cart_product_data',
  'post_add_to_cart',
  'save_cart_content_pre',
  'pre_place_order',
  'get_orders_post',
  'get_order_info',
  'change_order_status'
);
