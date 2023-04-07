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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_wishlist_fill_user_fields(&$exclude)
{
    $exclude[] = 'wishlist';
}

//
//
//
function fn_wishlist_get_gift_certificate_info(&$_certificate, &$certificate, &$type)
{
    if ($type == 'W' && is_numeric($certificate)) {
        $_certificate = fn_array_merge(Tygh::$app['session']['wishlist']['gift_certificates'][$certificate], array('gift_cert_wishlist_id' => $certificate));
    }
}

// [csmarket] fixes from 4.16.2
function fn_wishlist_user_init(&$auth, &$user_info, &$first_init)
{
    $user_id = $auth['user_id'];
    $user_type = 'R';
    if (empty($user_id) && fn_get_session_data('cu_id')) {
        $user_id = fn_get_session_data('cu_id');
        $user_type = 'U';
    }

    fn_extract_cart_content(Tygh::$app['session']['wishlist'], $user_id, 'W', $user_type);
}

function fn_wishlist_init_user_session_data(&$sess_data, &$user_id)
{
    $is_acting_on_behalf_of_user = !empty($sess_data['auth']['act_as_user'])
        && !empty($sess_data['auth']['area'])
        && $sess_data['auth']['area'] == 'C';
    if (AREA == 'C' || $is_acting_on_behalf_of_user) {
        if (empty(Tygh::$app['session']['wishlist'])) {
            Tygh::$app['session']['wishlist'] = array(
                'products' => array()
            );
        }
        fn_extract_cart_content($sess_data['wishlist'], $user_id, 'W');
        fn_save_cart_content(Tygh::$app['session']['wishlist'], $user_id, 'W');
    }

    return true;
}

function fn_wishlist_sucess_user_login($udata, $auth)
{
    if (AREA == 'C') {
        if ($cu_id = fn_get_session_data('cu_id')) {
            fn_clear_cart($cart);
            fn_save_cart_content($cart, $cu_id, 'W', 'U');
        }
    }
}

function fn_wishlist_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    $wishlist = & Tygh::$app['session']['wishlist'];

    if (!empty($wishlist['products'])) {
    foreach ($wishlist['products'] as $key => $product) {
        if (!empty($product['extra']['custom_files'])) {
        foreach ($product['extra']['custom_files'] as $option_id => $files) {
            if (!empty($files)) {
            foreach ($files as $file) {
                $product_data['custom_files']['uploaded'][] = array(
                    'product_id' => $key,
                    'option_id' => $option_id,
                    'path' => $file['path'],
                    'name' => $file['name'],
                );
            }
            }
        }
        }
    }
    }
}

//
// Add possibility to retrieve the wishlist products form user_sessions_products
//
// @param array $type_restrictions allowed types
// @return no return value
//
function fn_wishlist_get_carts(&$type_restrictions)
{
    if (is_array($type_restrictions)) {
        $type_restrictions[] = 'W';
    }
}

function fn_wishlist_get_additional_information(&$product, &$products_data)
{
    $_product = reset($products_data['product_data']);
    if (isset($product['product_id']) && isset($_product['product_id']) && $product['product_id'] == $_product['product_id'] && isset($_product['object_id'])) {
        $product['product_id'] = $product['object_id'] = $_product['object_id'];
    }
}

// [cs-market] changes from 4.16.2
/**
 * Gets wishlist items count
 *
 * @return int wishlist items count
 */
function fn_wishlist_get_count()
{
    $wishlist = [];
    $result = 0;

    if (!empty(Tygh::$app['session']['wishlist'])) {
        $wishlist = & Tygh::$app['session']['wishlist'];
    }

    if (
        !empty(Tygh::$app['session']['auth']['user_id'])
        && !empty($wishlist['products'])
    ) {
        $wishlist['products'] = fn_wishlist_get_wishlist_from_db(
            $wishlist['products'],
            Tygh::$app['session']['auth']['user_id']
        );
    }

    $result = !empty($wishlist['products']) ? count($wishlist['products']) : 0;

    /**
     * Changes wishlist items count
     *
     * @param array $wishlist wishlist data
     * @param int   $result   wishlist items count
     */
    fn_set_hook('wishlist_get_count_post', $wishlist, $result);

    return empty($result) ? -1 : $result;
}

/**
 * Retrieves the users wishlist from the database.
 *
 * @param array<int, array<string, string|array<int, string>|array<string, array<int, string>>>> $products Wishlist products from the user session
 * @param int                                                                                    $user_id  User ID
 *
 * @return array<int, array<string, string|array<int, string>|array<string, array<int, string>>>>
 */
function fn_wishlist_get_wishlist_from_db($products, $user_id)
{
    $condition = fn_user_session_products_condition([
        'user_id'             => $user_id,
        'type'                => "W",
        'user_type'           => "R",
        'get_session_user_id' => false,
        'get_session_id'      => false,
    ]);

    $wishlist_from_db = db_get_fields(
        'SELECT item_id FROM ?:user_session_products WHERE 1=1 AND ?p',
        $condition
    );

    foreach ($wishlist_from_db as $item_key => $product_key) {
        if (isset($products[$product_key])) {
            $wishlist_from_db[$product_key] = $products[$product_key];
        }
        unset($wishlist_from_db[$item_key]);
    }

    return $wishlist_from_db;
}

/**
 * The "save_cart_content_pre" hook handler.
 *
 * Actions performed:
 *  - Gets user data info from session and adds them into records with wishlist type
 *
 * @see fn_save_cart_content
 */
function fn_wishlist_save_cart_content_pre(&$cart, $user_id, $type, $user_type)
{
    if (!empty($user_id)) {
        return;
    }

    if ($type == 'W') {
        if (empty($cart['user_data']) && !empty(Tygh::$app['session']['cart']['user_data'])) {
            $cart['user_data'] = Tygh::$app['session']['cart']['user_data'];
        }
    } elseif (!empty(Tygh::$app['session']['wishlist']) && !empty($cart['user_data'])) {
        Tygh::$app['session']['wishlist']['user_data'] = $cart['user_data'];
        fn_save_cart_content(Tygh::$app['session']['wishlist'], $user_id, 'W', $user_type);
    }
}