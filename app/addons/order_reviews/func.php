<?php

use Tygh\Enum\SiteArea;
use Tygh\Enum\YesNo;
use Tygh\Enum\Addons\Discussion\DiscussionObjectTypes;

defined('BOOTSTRAP') or die('Access denied');

function fn_order_reviews_get_order_info(&$order, $additional_data) {
    if (SiteArea::isStorefront(AREA)) {
        $order['allow_order_review'] = true;
        if (fn_allowed_for('MULTIVENDOR')) {
            $order['allow_order_review'] = YesNo::toBool(db_get_field('SELECT allow_order_reviews FROM ?:companies WHERE company_id = ?i', $order['company_id']));
        }
        if ($order['allow_order_review'] && $discussion = fn_get_discussion($order['order_id'], DiscussionObjectTypes::ORDER)) {
            $order['allow_order_review'] = false;
        }
    }
}
