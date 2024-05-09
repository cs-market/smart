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
                fn_gather_additional_product_data($product);
                foreach ($fields as $field) {
                    if (isset($product[$field])) {
                        $product[$field] = fn_format_price($product[$field], CART_PRIMARY_CURRENCY, null, false);
                        $product[$field . '_formatted'] = fn_storefront_rest_api_format_price($product[$field], CART_PRIMARY_CURRENCY);
                    }
                }
            }
        }

        return $response;
    }


    /**
     * Calculates cart content with promotions, taxes and shipping.
     *
     * @param array<string, int|string|array> $cart      Cart data
     * @param array<string, int|string|array> $params    Calculation parameters
     * @param string                          $lang_code Two-letter language code
     *
     * @return array<string, int|string|array>
     */
    protected function calculate(array $cart, array $params = [], $lang_code = DEFAULT_LANGUAGE)
    {
        $cart = $this->setUserInfo($cart, fn_get_user_info($this->auth['user_id']));
        $cart['default_location'] = $this->getDefaultLocation($lang_code);

        // add payment methods
        $cart['payments'] = $this->getPayments($cart, $lang_code);

        // remove sensitive and redundant information
        $cart = fn_storefront_rest_api_strip_service_data($cart);


        return $cart;
    }
}
