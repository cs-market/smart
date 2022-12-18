<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'update' && isset($_REQUEST['plan'])) {
        $plan = $_REQUEST['plan'];

        //original plan
        $condition = '';
        if (Registry::get('runtime.company_id')) {
            $condition .= db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
        }
        $original_plan = db_get_fields("SELECT company_id from ?:sales_plan WHERE user_id =?i $condition", $_REQUEST['user_id']);

        $plan_companies = array();

        foreach ($plan as $key => &$value) {
            if ($value['frequency']) {
                if (!isset($value['company_id'])) $value['company_id'] = $key;
                $value['user_id'] = $_REQUEST['user_id'];
                db_query('REPLACE INTO ?:sales_plan ?e', $value);
                $plan_companies[] = $key;
            }
        }
        $deleted_companies = array_diff($original_plan, $plan_companies);
        if ($deleted_companies) {
            db_query('DELETE FROM ?:sales_plan WHERE user_id = ?i AND company_id in (?a)', $_REQUEST['user_id'], $deleted_companies);
        }
    }
}
