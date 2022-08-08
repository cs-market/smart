<?php

use Tygh\Registry;

function fn_exim_smart_auth_get_primary_object_id_sync_email_login(&$object, &$skip_get_primary_object_id, &$alt_keys) {
    $company_id = !empty($object['company_id']) ? $object['company_id'] : Registry::get('runtime.company_id');

    if ($company_id) {
        $alt_keys['company_id'] = $company_id;
    }
}
