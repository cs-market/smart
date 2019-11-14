<?php

use Tygh\Registry;

function fn_category_promotion_get_products_pre(&$params, $items_per_page, $lang_code)
{
  if (AREA !== 'A') {
    if (isset($params['category_id']) && $params['category_id']) {
      $is_category_promotion = in_array(
        $params['category_id'],
        explode(
          ',',
          Registry::get('addons.category_promotion.category_ids')
        )
      );

      if ($is_category_promotion) {
        unset($params['category_id'], $params['cid']);
        $params['category_promotion'] = true;
        if (isset($params['custom_extend'])) {
          $params['custom_extend'][] = 'prices';
        } else {
          $params['extend'][] = 'prices';
        }
      }
    }
  }
}

function fn_category_promotion_get_products($params, $fields, $sortings, &$condition, $join, $sorting, $group_by, $lang_code, $having)
{

  if (isset($params['category_promotion']) && $params['category_promotion']) {
    if (strpos($join, 'as prices') === false) {
      $condition .= db_quote(' AND products.list_price > ?:product_prices.price');
    } else {
      $condition .= db_quote(' AND products.list_price > prices.price');
    }
  }
}