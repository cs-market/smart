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

use Tygh\Languages\Languages;
use Tygh\Enum\ProductTracking;
use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var string $mode
 * @var string $action
 * @var array  $auth
 */
if ($mode == 'generate') {
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_id = (int) $_REQUEST['product_id'];
    $options_variant_ids = isset($_REQUEST['options_variant_ids']) ? (array) $_REQUEST['options_variant_ids'] : array();
    $variation_codes = isset($_REQUEST['variation_codes']) ? (array) $_REQUEST['variation_codes'] : array();
    $combinations = $product_options = array();

    $product_data = fn_get_product_data($product_id, $auth, CART_LANGUAGE, '', false, false, false, false, false, false, false, false);

    if (empty($product_data['product_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $options_result = fn_product_variations_get_available_options($product_data['product_id']);

    if ($options_result->isSuccess()) {
        $product_options = $options_result->getData();
        $combinations = fn_product_variations_get_options_combinations($product_data, $product_options);
    } else {
        $options_result->showNotifications();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $variations = array();
        $index = fn_product_variations_get_last_product_code_index($product_data['product_id']);

        foreach ($variation_codes as $variation_code) {
            if (isset($combinations[$variation_code]) && empty($combinations[$variation_code]['exists'])) {
                $index++;
                $combination = $combinations[$variation_code];

                $variations[$variation_code] = fn_product_variations_get_variation_by_selected_options(
                    $product_data,
                    $product_options,
                    $combination['selected_options'],
                    $index
                );
            }
        }

        if (!empty($variations)) {
            fn_product_variations_generate($product_id, $variations, array_keys($product_options));

            $product_manager->actualizeConfigurableProductAmount((array) $product_id);
        } else {
            fn_set_notification('E', __('error'), __('product_variations.please_select_combinations'));
        }

        return array(
            CONTROLLER_STATUS_REDIRECT,
            "products.update?product_id={$product_id}&selected_section=variations",
        );
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $view->assign(array(
        'expand_all' => false,
        'product_data' => $product_data,
        'product_options' => $product_options,
        'combinations' => $combinations
    ));
} elseif ($mode === 'list') {
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $product_id = isset($_REQUEST['product_id']) ? (int) $_REQUEST['product_id'] : 0;

    if (empty($product_id)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $params = array_merge($_REQUEST, array(
        'product_type'      => ProductManager::PRODUCT_TYPE_VARIATION,
        'parent_product_id' => $product_id,
    ));

    list($products, $search) = fn_get_products($params);
    fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

    $view
        ->assign('product_id', $product_id)
        ->assign('products', $products)
        ->assign('search', $search);
} elseif ($mode === 'convert') {
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    $product_id = isset($_REQUEST['product_id']) ? (int) $_REQUEST['product_id'] : 0;

    if ($product_id <= 0) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $product_data = fn_get_product_data($product_id, $auth);

    if (empty($product_data) || $product_data['product_type'] !== ProductManager::PRODUCT_TYPE_SIMPLE) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        fn_product_variations_convert_to_configurable_product($product_id);
        $product_manager->actualizeConfigurableProductAmount((array) $product_id);

        fn_set_notification('N', __('notice'), __('product_variations.convert_to_configurable_product_success'));
    }

    return array(CONTROLLER_STATUS_REDIRECT, "products.update?product_id={$product_id}");
}

/**
 * Generates variations of a product and saves those variations to the database.
 *
 * @param int   $product_id   Product identifier
 * @param array $combinations List of available combinations
 * @param array $options_ids  List of option identifier
 */
function fn_product_variations_generate($product_id, $combinations, array $options_ids)
{
    /** @var ProductManager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];

    if (!empty($combinations) && !empty($options_ids)) {
        $languages = Languages::getAll();
        $product_row = db_get_row('SELECT * FROM ?:products WHERE product_id = ?i', $product_id);
        $default_product_variation = $product_manager->getDefaultVariationOptions($product_id);

        foreach ($combinations as $variation_code => $combination) {
            $combination['is_default_variation'] = ($default_product_variation) ? 'N' : 'Y';
            fn_product_variations_save_variation($product_row, $combination, $languages);

            $default_product_variation = true;
        }

        $product_manager->changeProductTypeToConfigurable($product_id, array_values($options_ids));
    }
}

/**
 * Saves product variation by product combination.
 *
 * @param array $parent_product_data Parent product data
 * @param array $combination         Product combination data
 * @param array $languages           List of languages
 *
 * @return int
 */
function fn_product_variations_save_variation($parent_product_data, array $combination, $languages)
{
    $data = array_merge($parent_product_data, array(
        'product_id'           => null,
        'tracking'             => ProductTracking::TRACK_WITHOUT_OPTIONS,
        'product_type'         => ProductManager::PRODUCT_TYPE_VARIATION,
        'parent_product_id'    => $parent_product_data['product_id'],
        'variation_code'       => $combination['variation'],
        'variation_options'    => json_encode($combination['selected_options']),
        'timestamp'            => time(),
        'updated_timestamp'    => time(),
        'list_price'           => $combination['list_price'],
        'weight'               => $combination['weight'],
        'amount'               => isset($combination['amount']) ? $combination['amount'] : 1,
        'product_code'         => $combination['code'],
        'is_default_variation' => empty($combination['is_default_variation']) ? 'N' : $combination['is_default_variation']
    ));

    $product_variation_id = db_query('INSERT INTO ?:products ?e', $data);

    fn_update_product_prices($product_variation_id, array(
        'price'  => $combination['price'],
        'prices' => array(),
    ));

    foreach ($languages as $lang_code => $lang) {
        $description_data = array(
            'product_id' => $product_variation_id,
            'company_id' => $data['company_id'],
            'lang_code'  => $lang_code,
            'product'    => $combination['name'],
        );

        db_query('INSERT INTO ?:product_descriptions ?e', $description_data);
    }

    return $product_variation_id;
}

