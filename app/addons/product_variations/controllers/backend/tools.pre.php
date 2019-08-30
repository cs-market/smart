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
use Tygh\Common\OperationResult;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * @var string $mode
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'update_status') {
        $table = isset($_REQUEST['table']) ? $_REQUEST['table'] : null;
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

        if ($table === 'product_files' && $status === 'D') {
            /** @var \Tygh\Addons\ProductVariations\Product\Manager $product_manager */
            $product_manager = Tygh::$app['addons.product_variations.product.manager'];

            $product_id = (int) db_get_field('SELECT product_id FROM ?:product_files WHERE file_id = ?i', $id);

            $product_type = $product_manager->getProductFieldValue($product_id, 'product_type');

            if ($product_type === ProductManager::PRODUCT_TYPE_CONFIGURABLE) {
                $cnt = fn_product_variations_get_product_files_count($product_id, 'A', array($id));

                if (empty($cnt)) {
                    fn_set_notification('E', __('error'), __('product_variations.error.configurable_product_must_have_file'));

                    if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
                        exit;
                    } else {
                        return array(CONTROLLER_STATUS_REDIRECT, fn_url('products.update?product_id=' . $product_id . '&selected_section=files'));
                    }
                }
            }
        }

        if ($table === 'product_options' && $status === 'D') {            
            $result = fn_product_variations_can_disable_product_option($id);

            if (!$result->isSuccess()) {
                $result->showNotifications();
                if (defined('AJAX_REQUEST') && AJAX_REQUEST) {
                    exit;
                } else {
                    return array(CONTROLLER_STATUS_REDIRECT, fn_url('product_options.manage'));
                }
            }
        }
    }

    return array(CONTROLLER_STATUS_OK);
}