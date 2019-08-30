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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var string $mode
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'delete_file') {
        /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
        $product_manager = Tygh::$app['addons.product_variations.product.manager'];

        $product_id = isset($_REQUEST['product_id']) ? (int) $_REQUEST['product_id'] : 0;
        $file_id = isset($_REQUEST['file_id']) ? (int) $_REQUEST['file_id'] : 0;

        if ($product_id && $file_id) {
            $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

            if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
                $cnt = fn_product_variations_get_product_files_count($product_id, 'A', array($file_id));

                if (empty($cnt)) {
                    fn_set_notification('E', __('error'), __('product_variations.error.configurable_product_must_have_file'));

                    return array(CONTROLLER_STATUS_REDIRECT, fn_url('products.update?product_id=' . $product_id . '&selected_section=files'));
                }
            }
        }
    }

    return array(CONTROLLER_STATUS_OK);
}

if ($mode === 'update'
    && fn_allowed_for('MULTIVENDOR')
    && defined('AJAX_REQUEST')
    && isset($_REQUEST['product_id'])
) {
    /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
    $product_manager = Tygh::$app['addons.product_variations.product.manager'];
    $product_type = $product_manager->getProductTypeInstanceByProductId($_REQUEST['product_id']);

    Tygh::$app['view']->assign('product_type', $product_type);
}
