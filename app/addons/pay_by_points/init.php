<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
  'pre_add_to_cart',
  'check_add_to_cart_post',
  'add_to_cart',
  'get_cart_product_data'
);
