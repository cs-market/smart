<?php

use Tygh\Enum\YesNo;

function fn_returns_allowed() {
    $result = false;
    $company_id = $_SESSION['auth']['company_id'];
    if (!$company_id) {
        // check company by orders
    }
    if ($company_id) {
        $support_returns = db_get_field('SELECT support_returns FROM ?:companies WHERE company_id = ?i', $company_id);
        $result = YesNo::toBool($support_returns);
    }

    return $result;
}
