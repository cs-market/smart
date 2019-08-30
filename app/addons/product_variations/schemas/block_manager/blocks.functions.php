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

use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;

/**
 * Fetches variation products additional data for variations_filling in products block
 *
 * @param array $products Array of products
 * @param array $params Array of parameters
 */
function fn_product_variations_blocks_prepare_variations_list(&$products, $params)
{
    $product = reset($products);
    $parent_product_id = !empty($product['parent_product_id']) ? $product['parent_product_id'] : null;
    unset($product);

    if (!$parent_product_id) {
        return;
    }

    list($parent_product_data) = fn_get_products(array('pid' => $parent_product_id));
    $parent_product_data = !empty($parent_product_data[$parent_product_id])
        ? $parent_product_data[$parent_product_id]
        : array();

    if (!$parent_product_data) {
        return;
    }

    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    foreach ($products as $product_id => $product) {
        $products[$product_id] = $parent_product_data;
        $products[$product_id]['selected_options'] = $product_manager->getProductVariationOptions($product);
    }

    fn_gather_additional_products_data($products, $params);

    foreach ($products as $product_id => $product) {
        // "product_id" must be from parent product to be able to add to cart from the block
        // "variation_product_id" is required for generating proper product URL
        $products[$product_id]['for_variations_list'] = true;
    }
}
