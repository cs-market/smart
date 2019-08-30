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

        $icon_sizes = $this->safeGet($params, 'icon_sizes', [
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

            $product = $this->setProductIcons($product, $icon_sizes);
        }
        unset($product);

        if ($id) {
            $result['data'] = reset($products);
        } else {
            $result['data']['products'] = $products;
        }

        return $result;
    }

    /**
     * Adds icons for detailed and additional product images.
     *
     * @param array $product Product data to inject icons into
     * @param array $sizes   Icon sizes
     *
     * @return array Product data with image icons
     */
    protected function setProductIcons(array $product, array $sizes)
    {
        // main image
        if (!empty($product['main_pair'])) {
            $product['main_pair']['icons'] = $this->generateIcons($product['main_pair']['detailed'], $sizes['main_pair']);
        }

        // additional images
        if (!empty($product['image_pairs'])) {
            foreach ($product['image_pairs'] as &$pair) {
                $pair['icons'] = $this->generateIcons($pair['detailed'], $sizes['image_pairs']);
            }
            unset($pair);
        }

        // option combintaions images
        if (!empty($product['product_options'])) {
            foreach ($product['product_options'] as &$option) {
                if (!empty($option['variants'])) {
                    foreach ($option['variants'] as &$variant) {
                        if (!empty($variant['image_pair'])) {
                            $variant['image_pair']['icons'] = $this->generateIcons($variant['image_pair']['icon'], $sizes['image_pairs']);
                        }
                    }
                    unset($variant);
                }
            }
            unset($option);
        }

        // features images
        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as &$feature) {
                if (!empty($feature['variants'])) {
                    foreach ($feature['variants'] as &$variant) {
                        if (!empty($variant['image_pairs'])) {
                            $variant['image_pairs']['icons'] = $this->generateIcons($variant['image_pairs']['icon'], $sizes['image_pairs']);
                        }
                    }
                    unset($variant);
                }
            }
            unset($feature);
        }

        return $product;
    }

    /**
     * Generates icons in selected sizes.
     *
     * @param array $image_data Image to generate icons for
     * @param array $sizes      Icon sizes
     *
     * @return array
     */
    protected function generateIcons(array $image_data, array $sizes)
    {
        $icons = [];

        foreach ($sizes as list($width, $height)) {
            $icons["{$width}x{$height}"] =
                fn_image_to_display($image_data, $width, $height);
        }

        return $icons;
    }
}
