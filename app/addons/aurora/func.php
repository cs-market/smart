<?php

use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_mobile_app_links() {
    if ($company_id = Tygh::$app['session']['auth']['company_id']) {
        $links = db_get_row('SELECT app_store, play_market, app_gallery FROM ?:companies WHERE company_id = ?i', $company_id);
        return array_filter($links);
    }
}

function fn_blocks_aurora_get_vendor_info() {
    $company_id = !empty(Tygh::$app['session']['auth']['company_id']) ? Tygh::$app['session']['auth']['company_id'] : null;

    $company_data = [];
    $company_data['logos'] = fn_get_logos($company_id);

    if (!is_file($company_data['logos']['theme']['image']['absolute_path'])) {
        $company_data['logos'] = fn_get_logos(null);
    }

    return $company_data;
}
