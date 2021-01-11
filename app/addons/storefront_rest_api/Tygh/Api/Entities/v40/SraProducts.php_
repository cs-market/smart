<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Api\Entities\v40;

use Tygh\Api\Entities\Products;

/**
 * Class SraProducts implements API entity to provide products data.
 *
 * @package Tygh\Api\Entities
 */
class SraProducts extends Products
{
    protected $icon_size_small = [500, 500];
    protected $icon_size_big = [1000, 1000];


    /** @inheritdoc */
    public function index($id = 0, $params = array())
    {
        $result = parent::index($id, $params);

        $is_discussion_enabled = SraDiscussion::isAddonEnabled();

        $params['icon_sizes'] = $this->safeGet($params, 'icon_sizes', [
            'main_pair'   => [$this->icon_size_big, $this->icon_size_small],
            'image_pairs' => [$this->icon_size_small],
        ]);

        $products = [];
        if ($id && !empty($result['data'])) {
            $products = [$result['data']['product_id'] => $result['data']];

            fn_gather_additional_products_data($products, [
                'get_options'         => true,
                'get_features'        => true,
                'get_detailed'        => true,
                'get_icon'            => true,
                'get_additional'      => true,
                'features_display_on' => 'A',
            ]);
        } elseif (!empty($result['data']['products'])) {
            $products = $result['data']['products'];
        }

        foreach ($products as &$product) {
            $product = fn_storefront_rest_api_format_product_prices($product);

            if ($is_discussion_enabled) {
                $product = SraDiscussion::setDiscussionType($product, DISCUSSION_OBJECT_TYPE_PRODUCT);
            }

            $product = fn_storefront_rest_api_set_product_icons($product, $params['icon_sizes']);
        }
        unset($product);

        if ($id) {
            $result['data'] = reset($products);
        } else {
            $result['data']['products'] = $products;
        }

        return $result;
    }
}
