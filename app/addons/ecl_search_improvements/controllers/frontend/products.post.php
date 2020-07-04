<?php
/*****************************************************************************
*                                                                            *
*                   All rights reserved! eCom Labs LLC                       *
* http://www.ecom-labs.com/about-us/ecom-labs-modules-license-agreement.html *
*                                                                            *
*****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'search') {

    $params = $_REQUEST;
    if (!empty($_REQUEST['q'])) {
        $key_word_exists = db_get_field("SELECT key_word_id FROM ?:search_key_words WHERE key_word = ?s AND lang_code = ?s", $_REQUEST['q'], CART_LANGUAGE);
        if (!empty($key_word_exists)) {
            db_query("UPDATE ?:search_key_words SET popularity = popularity + 1, timestamp = ?i WHERE key_word = ?s AND lang_code = ?s", TIME, $_REQUEST['q'], CART_LANGUAGE);
        } else {
            $_data = array(
                'key_word' => $_REQUEST['q'],
                'timestamp' => TIME,
                'lang_code' => CART_LANGUAGE,
                'popularity' => 1,
                'company_id' => Registry::get('runtime.company_id')
            );
            db_query("INSERT INTO ?:search_key_words ?e", $_data);
        }
    }
    
    if (!empty($params['search_performed']) && Registry::get('addons.ecl_search_improvements.single_product') == 'Y' && (empty($params['page']) || $params['page'] == 1) && empty($params['features_hash'])) {
        $products = Registry::get('view')->getTemplateVars('products');
        
        if (count($products) == 1) {
            $product = reset($products);
            fn_redirect('products.view?product_id=' . $product['product_id']);
        }
    }
}