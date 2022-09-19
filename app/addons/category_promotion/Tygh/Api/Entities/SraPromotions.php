<?php

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class SraPromotions extends AEntity
{
    protected $icon_size_small = [500, 500];
    protected $icon_size_big = [1000, 1000];
    
    public function index($id = 0, $params = array())
    {
        $data = ['promotions' => [], 'products' => []];
        unset($params['items_per_page'], $params['page']);

        $lang_code = $this->getLanguageCode($params);
        $currency = $this->getCurrencyCode($params);
        $params['icon_sizes'] = $this->safeGet($params, 'icon_sizes', [
            'main_pair'   => [$this->icon_size_big, $this->icon_size_small],
            'image_pairs' => [$this->icon_size_small],
        ]);

        $params = fn_array_merge($params, [
            'active'     => true,
            /*'zone' => 'catalog',*/
            'mode'       => 'list',
            'extend'     => ['get_images'],
            'sort_by' => 'priority',
            'sort_order' => 'asc',
        ]);
        if ($id) {
            $promotion = fn_get_promotion_data($id, $lang_code);
            $product_ids = $promotion['products'];
            if ($product_ids) {
                $s_params = $_REQUEST;
                $s_params['extend'] = ['categories', 'description'];
                $s_params['pid'] = explode(',', $product_ids);
                unset($s_params['items_per_page'], $s_params['page']);
                list($products, $search) = fn_get_products($s_params);
            }
            // $data['promotions'][] = $promotion;
        } else {
            list($promotions) = fn_get_promotions($params);
            list($data['promotions'], $products) = fn_category_promotion_split_promotion_by_type($promotions);
        }


        if (!empty($products)) {
            foreach ($products as &$product) {
                $amount = $this->getRequestedProductAmount($params, $product['product_id']);
                if ($amount > 1) {
                    $product['price'] = fn_get_product_price($product['product_id'], $amount, $this->auth);
                }
            }
            unset($product);

            $products = fn_storefront_rest_api_gather_additional_products_data($products, $params);

            foreach ($products as &$product) {
                $amount = $this->getRequestedProductAmount($params, $product['product_id']);
                if ($amount > 1) {
                    $product = $this->calculateQuantityPrice($product, $amount);
                }

                $product = fn_storefront_rest_api_format_product_prices($product, $currency);

                $product = fn_storefront_rest_api_set_product_icons($product, $params['icon_sizes']);
            }
            unset($product);

            $data['products'] = $products;
        }

        return array(
            'status' => Response::STATUS_OK,
            'data' => $data
        );
    }

    protected function getRequestedProductAmount($params, $product_id)
    {
        $amount = 1;
        if (isset($params['amount'][$product_id])) {
            $amount = (int) $params['amount'][$product_id];
        } elseif (isset($params['amount'])) {
            $amount = (int) $params['amount'];
        }

        return $amount;
    }

    public function create($params)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function update($id, $params)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function delete($id)
    {
        return array(
            'status' => Response::STATUS_FORBIDDEN,
            'data' => []
        );
    }

    public function privileges()
    {
        return array(
            'create' => false,
            'update' => false,
            'delete' => false,
            'index'  => true
        );
    }
    public function privilegesCustomer()
    {
        return [
            'index'  => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }
}
