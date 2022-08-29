<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\v40\SraCartContent as BaseSraCartContent;
use Tygh\Api\Response;
use Tygh\Common\OperationResult;

class SraCartContent extends BaseSraCartContent
{
    protected function addProducts(array $cart, array $cart_products, $is_update = false)
    {
        $old_cart_products = [];
        if ($cart['products']) {
            $old_cart_products = array_column($cart['products'], 'amount', 'item_id');
        }

        $operation_result = new OperationResult(false);

        $product_cart_ids = fn_add_product_to_cart($cart_products, $cart, $this->auth, $is_update);
        if ($product_cart_ids) {
            $added = [];
            foreach($product_cart_ids as $item_id => $product_id) {
                $old_amount = ($old_cart_products[$item_id]) ?? 0;
                $added[$item_id] = $cart['products'][$item_id]['amount'] - $old_amount;
            }
            $operation_result->setSuccess(true);
            $operation_result->setData($product_cart_ids, 'cart_ids');
            $operation_result->setData($added, 'added_amount');

            $this->save($cart);
        }

        return $operation_result;
    }

    protected function updateProducts(array $cart_products, $action = self::PRODUCT_ACTION_ADD)
    {
        $product_cart_ids = $added_amount = [];

        $operation_result = new OperationResult(true);

        $cart_products = fn_storefront_rest_api_group_cart_products($cart_products);

        foreach ($cart_products as $group) {
            $cart = $this->get($group['cart_service_id']);
            $group_result = $this->addProducts($cart, $group['products'], $action === self::PRODUCT_ACTION_UPDATE);

            if (!$group_result->isSuccess()) {
                $operation_result->setSuccess(false);
                $operation_result->setErrors($group_result->getErrors());
                break;
            }

            $product_cart_ids = fn_array_merge($product_cart_ids, $group_result->getData('cart_ids'));
            $added_amount = fn_array_merge($added_amount, $group_result->getData('added_amount'));
        }

        $operation_result->setData($product_cart_ids, 'cart_ids');
        $operation_result->setData($added_amount, 'added_amount');

        return $operation_result;
    }

    public function create($params)
    {
        if ($user_relation_error = $this->getUserRelationError()) {
            return $user_relation_error;
        }

        $status = Response::STATUS_BAD_REQUEST;
        $data = [];

        $cart_products = $this->safeGet($params, 'products', []);

        // add to cart
        if ($cart_products) {
            if (!$this->auth['user_id']) {
                return [
                    'status' => Response::STATUS_FORBIDDEN,
                    'data'   => [
                        'message' => __('storefront_rest_api.guests_cant_add_products_to_cart')
                    ]
                ];
            }

            $result = $this->updateProducts($cart_products, self::PRODUCT_ACTION_ADD);
            if ($result->isSuccess()) {
                $status = Response::STATUS_CREATED;
                $data['cart_ids'] = $result->getData('cart_ids');
                $data['added_amount'] = $result->getData('added_amount');
            } else {
                $data['message'] = $result->getFirstError();
                $status = Response::STATUS_CONFLICT;
            }
        } else {
            $data['message'] = __('api_required_field', [
                '[field]' => 'products',
            ]);
        }

        return [
            'status' => $status,
            'data'   => $data,
        ];
    }
}
