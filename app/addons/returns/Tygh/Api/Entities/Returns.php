<?php

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;

class Returns extends AEntity
{
    public function index($id = 0, $params = array())
    {
        $lang_code = $this->getLanguageCode($params);
        $currency = $this->getCurrencyCode($params);

        $params['icon_sizes'] = $this->safeGet($params, 'icon_sizes', [
            'main_pair'   => [$this->icon_size_big, $this->icon_size_small],
            'image_pairs' => [$this->icon_size_small],
        ]);

        list($products) = fn_get_products(['only_ordered' => true]);

        $products = fn_storefront_rest_api_gather_additional_products_data($products, $params);

        foreach($products as &$product) {
            $product = fn_storefront_rest_api_format_product_prices($product, $currency);
            $product = fn_storefront_rest_api_set_product_icons($product, $params['icon_sizes']);

            $product['selected_amount'] = 0;
            $product['min_qty'] = 0;
        }

        return array(
            'status' => Response::STATUS_OK,
            'data' => $products
        );
    }

    public function create($params)
    {
        $data = [];
        if (!empty($params)) {
            $return_id = fn_create_return($params, $this->auth);
            if ($return_id) {
                $data['message'] = strip_tags($message = __('return_added_successfully', ['[return_id]' => $return_id]));
            }
        }

        $status = Response::STATUS_OK;

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $status = Response::STATUS_NOT_FOUND;
        $data = array();

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    // public function privileges()
    // {
    //     $privileges = array(
    //         'create' => 'manage_vendors',
    //         'update' => 'manage_vendors',
    //         'delete' => 'manage_vendors',
    //         'index'  => 'view_vendors'
    //     );

    //     return $privileges;
    // }


    public function privilegesCustomer()
    {
        return [
            'index'  => true,
            'create' => true,
            'update' => false,
            'delete' => false,
        ];
    }
}
