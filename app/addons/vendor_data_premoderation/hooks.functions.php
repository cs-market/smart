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

use Tygh\Enum\Addons\VendorDataPremoderation\ProductStatuses;
use Tygh\Enum\VendorStatuses;
use Tygh\Enum\YesNo;
use Tygh\NotificationsCenter\NotificationsCenter;
use Tygh\Registry;
use Tygh\Tools\Url;
use Tygh\Tygh;
use Tygh\Tools\SecurityHelper;

defined('BOOTSTRAP') or die('Access denied');

/**
 * Hook handler: adds "Requires approval" and "Disapproved" product statuses.
 */
function fn_vendor_data_premoderation_get_all_product_statuses_post($lang_code, &$statuses)
{
    $statuses[ProductStatuses::REQUIRES_APPROVAL] = __(
        'vendor_data_premoderation.product_status.requires_approval',
        [],
        $lang_code
    );

    $statuses[ProductStatuses::DISAPPROVED] = __(
        'vendor_data_premoderation.product_status.disapproved',
        [],
        $lang_code
    );
}

/**
 * Hook handler: updates product moderation results when changing a product status.
 */
function fn_vendor_data_premoderation_tools_update_status_before_query(
    $params,
    $old_status,
    &$status_data,
    $condition
) {
    if ($params['table'] !== 'products'
        || $old_status === $params['status']
    ) {
        return;
    }

    if (fn_get_runtime_company_id()
        && ($old_status === ProductStatuses::REQUIRES_APPROVAL
            || $old_status === ProductStatuses::DISAPPROVED
        )
    ) {
        $status_data['status'] = $old_status;
        return;
    }

    $product_id = $params['id'];

    switch ($params['status']) {
        case ProductStatuses::REQUIRES_APPROVAL:
            // "Requires approval" status can't be set manually
            $status_data['status'] = $old_status;
            break;
        case ProductStatuses::DISAPPROVED:
            fn_vendor_data_premoderation_disapprove_products([$product_id], false);
            break;
        default:
            if (($old_status === ProductStatuses::REQUIRES_APPROVAL || $old_status === ProductStatuses::DISAPPROVED)
                && (empty($params['is_status_updated_during_approval']))
            ) {
                fn_vendor_data_premoderation_approve_products([$product_id], false);
            }
            break;
    }
}

/**
 * Hook handler: adds premoderation reason for products.
 */
function fn_vendor_data_premoderation_gather_additional_products_data_post(
    $product_ids,
    $params,
    &$products,
    $auth,
    $lang_code
) {
    if (AREA !== 'A') {
        return;
    }

    $premoderation = fn_vendor_data_premoderation_get_premoderation($product_ids);

    foreach ($products as &$product_data) {
        $product_data['premoderation_reason'] = isset($premoderation[$product_data['product_id']])
            ? $premoderation[$product_data['product_id']]['reason']
            : '';
    }
    unset($product_data);
}

/**
 * Hook handler: adds premoderation reason for a product.
 */
function fn_vendor_data_premoderation_get_product_data_post(&$product_data)
{
    if (AREA !== 'A' || !$product_data) {
        return;
    }

    $premoderation = fn_vendor_data_premoderation_get_premoderation($product_data['product_id']);

    $product_data['premoderation_reason'] = $premoderation
        ? $premoderation[$product_data['product_id']]['reason']
        : '';
}

/**
 * Hook handler: sets product approval status after product was cloned.
 */
function fn_vendor_data_premoderation_clone_product_post(
    $original_product_id,
    $cloned_product_id,
    $orig_name,
    $new_name
) {
    if (!$cloned_product_id || !fn_get_runtime_company_id()) {
        return;
    }

    if (fn_vendor_data_premoderation_product_requires_approval(Registry::get('runtime.company_data'), true)) {
        fn_vendor_data_premoderation_request_approval_for_products([$cloned_product_id], true);
    }
}

/**
 * The "update_product_pre" hook handler.
 *
 * Actions performed:
 *     - For vendors: stores originally passed product data when updating a product.
 *     - For admin: approves product when changing its status to something rather than Disapproved or Requires approval.
 *
 * @param array $product_data Product data
 * @param int   $product_id   Product identifier
 *
 * @see \fn_update_product()
 */
function fn_vendor_data_premoderation_update_product_pre(array &$product_data, $product_id)
{
    // remove previously stored initial product state
    Registry::del('vendor_data_premoderation.initial_product_state');
    // indicate that product update is performed
    Registry::set('vendor_data_premoderation.is_updating_product', true, true);

    if (!$product_id || !fn_get_runtime_company_id()) {
        return;
    }

    $current_status = fn_vendor_data_premoderation_get_current_product_statuses([$product_id])[$product_id];
    $requires_premoderation = fn_vendor_data_premoderation_product_requires_approval(Registry::get('runtime.company_data'), false, $current_status);

    // get initial state only when the product is updated and updates premoderation is required by the company settings
    if ($requires_premoderation) {
        $initial_product_state = fn_vendor_data_premoderation_get_product_state($product_id);
        Registry::set('vendor_data_premoderation.initial_product_state', $initial_product_state, true);
    }

    if (!isset($product_data['status'])) {
        return;
    }

    // Admin actions: approve or disapprove products
    $new_status = $product_data['status'];

    if (!fn_vendor_data_premoderation_is_product_status_can_be_changed($new_status, $current_status)) {
        unset($product_data['status']);
        return;
    }

    if (
        in_array($current_status, [ProductStatuses::REQUIRES_APPROVAL, ProductStatuses::DISAPPROVED])
        && !in_array($new_status, [ProductStatuses::REQUIRES_APPROVAL, ProductStatuses::DISAPPROVED])
    ) {
        fn_vendor_data_premoderation_approve_products([$product_id], false);
        return;
    }

    if ($new_status === ProductStatuses::DISAPPROVED && isset($product_data['premoderation_reason'])) {
        fn_vendor_data_premoderation_disapprove_products([$product_id], false, $product_data['premoderation_reason']);
        return;
    }
}

/**
 * The "update_product_post" hook handler.
 *
 * Actions performed:
 *     - Sets product approval status after product was updated
 *
 * @see \fn_update_product()
 */
function fn_vendor_data_premoderation_update_product_post($product_data, $product_id, $lang_code, $is_created)
{
    // reset product update indicator
    Registry::del('vendor_data_premoderation.is_updating_product');

    if (!$product_id) {
        return;
    }

    if (!fn_get_runtime_company_id()) {
        return;
    }

    unset($product_data['premoderation_reason']);

    $current_status = fn_vendor_data_premoderation_get_current_product_statuses([$product_id])[$product_id];
    $requires_premoderation = fn_vendor_data_premoderation_product_requires_approval(Registry::get('runtime.company_data'), $is_created, $current_status);
    if (!$is_created && $requires_premoderation) {
        $initial_product_state = Registry::ifGet('vendor_data_premoderation.initial_product_state', null);
        $resulting_product_state = fn_vendor_data_premoderation_get_product_state($product_id);
        $requires_premoderation = fn_vendor_data_premoderation_is_product_changed($initial_product_state, $resulting_product_state);
    }

    if (!$requires_premoderation) {
        return;
    }

    fn_vendor_data_premoderation_request_approval_for_products([$product_id], true);
}


/**
 * The "update_product_categories_pre" hook handler.
 *
 * Actions performed:
 *     - Stores initial product data when updating product categories outside of fn_update_product function.
 *
 * @see \fn_update_product_categories()
 */
function fn_vendor_data_premoderation_update_product_categories_pre($product_id, $product_data, $rebuild, $company_id)
{
    // do not perform any checks when fn_update_product_categories() is called within fn_update_product()
    if (Registry::isExist('vendor_data_premoderation.is_updating_product')) {
        return;
    }

    if (!fn_get_runtime_company_id()) {
        return;
    }

    $current_status = fn_vendor_data_premoderation_get_current_product_statuses([$product_id])[$product_id];
    $requires_premoderation = fn_vendor_data_premoderation_product_requires_approval(Registry::get('runtime.company_data'), false, $current_status);
    if (!$requires_premoderation) {
        return;
    }

    if ($current_status === ProductStatuses::REQUIRES_APPROVAL) {
        return;
    }

    $initial_product_state = fn_vendor_data_premoderation_get_product_state($product_id);
    Registry::set('vendor_data_premoderation.initial_product_state', $initial_product_state, true);
}

/**
 * The "update_product_categories_pre" hook handler.
 *
 * Actions performed:
 *     - Stores initial product data when updating product categories outside of fn_update_product function.
 *
 * @see \fn_update_product_categories()
 */
function fn_vendor_data_premoderation_update_product_categories_post($product_id, $product_data, $existing_categories, $rebuild, $company_id, $saved_category_ids)
{
    // do not perform any checks when fn_update_product_categories() is called within fn_update_product()
    if (Registry::isExist('vendor_data_premoderation.is_updating_product')) {
        return;
    }

    if (!fn_get_runtime_company_id()) {
        return;
    }

    $current_status = fn_vendor_data_premoderation_get_current_product_statuses([$product_id])[$product_id];
    $requires_premoderation = fn_vendor_data_premoderation_product_requires_approval(Registry::get('runtime.company_data'), false, $current_status);
    if (!$requires_premoderation) {
        return;
    }

    if ($current_status === ProductStatuses::REQUIRES_APPROVAL) {
        return;
    }

    $initial_product_state = Registry::ifGet('vendor_data_premoderation.initial_product_state', null);
    $resulting_product_state = fn_vendor_data_premoderation_get_product_state($product_id);
    $has_changes_to_moderate = fn_vendor_data_premoderation_is_product_changed($initial_product_state, $resulting_product_state);

    if (!$has_changes_to_moderate) {
        return;
    }

    fn_vendor_data_premoderation_request_approval_for_products([$product_id], true);
}

/**
 * Hook handler: filters out disapproved products from exported ones.
 */
function fn_vendor_data_premoderation_data_feeds_export_before_get_products($datafeed_data, $pattern, &$params)
{
    if (isset($params['status'])) {
        $params['status'] = (array) $params['status'];
    } else {
        $params['status'] = array_keys(fn_get_all_product_statuses());
    }

    if (isset($datafeed_data['params']['exclude_disapproved_products'])
        && YesNo::toBool($datafeed_data['params']['exclude_disapproved_products'])
    ) {
        $params['status'] = array_diff($params['status'], [ProductStatuses::REQUIRES_APPROVAL, ProductStatuses::DISAPPROVED]);
    } else {
        $params['status'] = array_unique(array_merge($params['status'], [ProductStatuses::REQUIRES_APPROVAL, ProductStatuses::DISAPPROVED]));
    }

    if (isset($datafeed_data['params']['exclude_disabled_products'])
        && YesNo::toBool($datafeed_data['params']['exclude_disabled_products'])
    ) {
        $params['status'] = array_diff($params['status'], [ProductStatuses::DISABLED, ProductStatuses::HIDDEN, ProductStatuses::DISAPPROVED]);
    }
}

/**
 * Hook handler: sets company moderation status when creating/updating.
 */
function fn_vendor_data_premoderation_update_company_pre(&$company_data, &$company_id, &$lang_code)
{
    if (!fn_get_runtime_company_id()) {
        return;
    }

    $orig_company_data = fn_get_company_data($company_id, $lang_code);
    $vendors_premoderation = Registry::get('addons.vendor_data_premoderation.vendor_profile_updates_approval');

    if (
        isset($company_data['status'])
        && !fn_vendor_data_premoderation_is_vendor_status_can_be_changed($company_data['status'], $orig_company_data['status'])
    ) {
        unset($company_data['status']);
    }

    if ($orig_company_data['status'] === VendorStatuses::ACTIVE
        && ($vendors_premoderation == 'all'
            || ($vendors_premoderation == 'custom'
                && !empty($orig_company_data['pre_moderation_edit_vendors'])
                && YesNo::toBool($orig_company_data['pre_moderation_edit_vendors'])
            )
        )
    ) {
        $logotypes = fn_filter_uploaded_data('logotypes_image_icon'); // FIXME: dirty comparison

        SecurityHelper::sanitizeObjectData('company', $company_data);

        // check that some data is changed
        if (fn_vendor_data_premoderation_diff_company_data($company_data, $orig_company_data) || !empty($logotypes)) {
            $company_data['status'] = VendorStatuses::PENDING;
        }
    }
}

/**
 * Hook handler: Notifies admin about products that require approval.
 */
function fn_vendor_data_premoderation_set_admin_notification(&$auth)
{
    if (!$auth['company_id'] && fn_check_permissions('premoderation', 'm_approve', 'admin')) {
        $count = db_get_field(
            'SELECT COUNT(*) FROM ?:products WHERE ?w',
            [
                'status' => ProductStatuses::REQUIRES_APPROVAL,
            ]
        );

        if (!$count) {
            return;
        }

        /** @var \Tygh\NotificationsCenter\NotificationsCenter $notifications_center */
        $notifications_center = Tygh::$app['notifications_center'];
        /** @var \Tygh\Tools\Formatter $formatter */
        $formatter = Tygh::$app['formatter'];

        $oldest_pending_timestamp = db_get_field(
            'SELECT MIN(premoderation.updated_timestamp)'
            . ' FROM ?:premoderation_products AS premoderation'
            . ' INNER JOIN ?:products AS products ON products.product_id = premoderation.product_id'
            . ' WHERE ?w',
            [
                'products.status' => ProductStatuses::REQUIRES_APPROVAL,
            ]
        );

        $notifications_center->add([
            'user_id'    => $auth['user_id'],
            'title'      => __('vendor_data_premoderation.notification.products_require_approval.title', [
                $count,
            ]),
            'message'    => __('vendor_data_premoderation.notification.products_require_approval.message', [
                $count,
                '[since]' => $formatter->asDatetime($oldest_pending_timestamp),
            ]),
            'area'       => 'A',
            'section'    => NotificationsCenter::SECTION_PRODUCTS,
            'tag'        => 'vendor_data_premoderation',
            'action_url' => Url::buildUrn(['products', 'manage'], ['status' => ProductStatuses::REQUIRES_APPROVAL]),
            'language_code' => Registry::get('settings.Appearance.backend_default_language'),
        ]);
    }
}

/**
 * Hook handler: adds "Requires approval" and "Disapproved" product statuses.
 */
function fn_vendor_data_premoderation_get_product_statuses_post($status, $add_hidden, $lang_code, &$statuses)
{
    static $company_id;
    if ($company_id === null) {
        $company_id = (int) fn_get_runtime_company_id();
    }

    // Vendors can't change product status if the product was sent to moderation
    if ($company_id !== 0
        && ($status === ProductStatuses::REQUIRES_APPROVAL
            || $status === ProductStatuses::DISAPPROVED
        )
    ) {
        $statuses = [];
    }

    // "Requires approval" status can't be set manually
    if ($status === ProductStatuses::REQUIRES_APPROVAL) {
        $statuses[ProductStatuses::REQUIRES_APPROVAL] = __(
            'vendor_data_premoderation.product_status.requires_approval',
            [],
            $lang_code
        );
    }

    // Only administrators can set product status to "Disapproved"
    if ($company_id === 0 && $status !== '' || $status === ProductStatuses::DISAPPROVED) {
        $statuses[ProductStatuses::DISAPPROVED] = __(
            'vendor_data_premoderation.product_status.disapproved',
            [],
            $lang_code
        );
    }
}

/**
 * Hook handler: removes product premoderation when deleting a product.
 */
function fn_vendor_data_premoderation_delete_product_post($product_id)
{
    fn_vendor_data_premoderation_delete_premoderation($product_id);
}
