<?php 

use Tygh\Registry;

if ($mode == 'cron') {
    $condition = db_quote(' AND autoimport = ?s', 'Y');
    if (!empty($action)) {
        $condition .= db_quote(' AND company_id = ?i', $action);
    }
    $companies = db_get_fields("SELECT company_id FROM ?:companies WHERE 1 $condition");

    foreach ($companies as $company_id) {
        $files = fn_auto_exim_find_files($company_id);

        if ($files) {
            fn_auto_exim_run_import($files, $company_id);
        }
    }
    exit ;
}
