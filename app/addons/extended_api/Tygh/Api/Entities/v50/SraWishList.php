<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Entities\v40\SraWishList as BaseSraWishList;
use Tygh\Api\Response;

class SraWishList extends BaseSraWishList
{
    /**
     * @var string $cart_type Wishlist cart type
     */
    protected $cart_type = 'W';

//     /** @inheritdoc */
    public function index($id = '', $params = array())
    {
        $response = parent::index($id);

        if ($response['status'] == Response::STATUS_OK) {
            $response['data'] = array(
                'products' => $response['data']['products'],
            );

            $fields = array(
                'list_price',
                'price',
                'base_price',
                'original_price',
                'display_price',
                'discount',
                'subtotal',
                'display_subtotal',
                'taxed_price',
                'clean_price'
            );
            foreach($response['data']['products'] as &$product) {
                $product = fn_get_product_data($product['product_id'], $this->auth, CART_LANGUAGE, '', true, true, true, false, false, true, false, true);
                foreach ($fields as $field) {
                    if (isset($product[$field])) {
                        $product[$field] = fn_format_price($product[$field], CART_PRIMARY_CURRENCY, null, false);
                    }
                }
            }
        }

        return $response;
    }
}
