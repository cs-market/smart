<?php

fn_register_hooks(
  'update_product_post',
  'get_product_data_post',
  'get_products_post',
  'gather_additional_product_data_before_discounts',
  'calculate_cart_items',
  'get_order_items_info_post'
);
