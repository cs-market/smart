<?php

fn_register_hooks(
  'update_product_post',
  'get_product_data_post',
  'get_products_post',
  'gather_additional_product_data_before_discounts',
  'get_order_items_info_post',
  'get_product_price_post',
  'delete_product_post',
  'post_delete_user',
  'storages_get_cart_product_data'
);
