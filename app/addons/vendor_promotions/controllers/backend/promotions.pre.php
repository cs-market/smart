<?php

use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update') {
        if (Registry::get('runtime.company_id')) {
            if (!empty($_REQUEST['promotion_id']) && !fn_check_company_id('promotions', 'promotion_id', $_REQUEST['promotion_id'])) {
                fn_company_access_denied_notification();
                return array(CONTROLLER_STATUS_REDIRECT, 'promotions.update?promotion_id=' . $_REQUEST['promotion_id']);
            }

        }
    }
}
