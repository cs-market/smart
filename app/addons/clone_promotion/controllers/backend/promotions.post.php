<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update') {
    Registry::set('navigation.dynamic.actions.clone_promotion', array (
        'href' => "promotions.clone?promotion_id=$_REQUEST[promotion_id]",
        'meta' => '',
        'target' => '',
    ));
}

if ($mode == 'clone') {
    if (AREA == 'A') {
        if (!empty($_REQUEST['promotion_id'])) {
            $promotion_id = $_REQUEST['promotion_id'];
            
            // Clone main data
            $data = db_get_row("SELECT * FROM ?:promotions WHERE promotion_id = ?i", $promotion_id);
            unset($data['promotion_id']);
            $data['status'] = 'D';
            $pid = db_query("INSERT INTO ?:promotions ?e", $data);

            // Clone descriptions
            $data = db_get_array("SELECT * FROM ?:promotion_descriptions WHERE promotion_id = ?i", $promotion_id);
            foreach ($data as $v) {
                $v['promotion_id'] = $pid;
                $v['name'] .= ' [CLONE]';
                db_query("INSERT INTO ?:promotion_descriptions ?e", $v);
            }
            
            if (!empty($pid)) {
                fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('promotion_cloned'));
            }

            return array(CONTROLLER_STATUS_REDIRECT, "promotions.update?promotion_id=$pid");
        }
    }
}
