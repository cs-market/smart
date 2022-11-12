<?php

use Tygh\Tygh;
use Tygh\Enum\SiteArea;

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

function fn_aurora_init_user_session_data(&$sess_data, $user_id) {
    $message = __(
        'vendor_panel_configurator.configure_vendor_panel_notice',
        [
            '[config_url]' => fn_url(
                'addons.update?addon=vendor_panel_configurator&selected_section=settings',
                SiteArea::ADMIN_PANEL
            ),
        ]
    );

    if (!empty($sess_data['notifications'])) {
        foreach ($sess_data['notifications'] as $key => $data) {
            if ($data['message'] == $message) {
                unset($sess_data['notifications'][$key]);
            }
        }
    }

}
