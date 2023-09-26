<?php

namespace Tygh\Addons\Telegram\Routes;

use Tygh\Registry;

class OrderStatus extends ARoute {
    public function render($id, $params, $context) {
        if (!empty($id)) {
            if (!empty($params['status'])) {
                if(fn_change_order_status($id, $params['status'])) {
                    $statuses = fn_get_simple_statuses();
                    $return['message'] = __('change_order_status_default_subj', ['[order]' => $id, '[status]' => $statuses[$params['status']]]);
                    $return['inline_keyboard'] = [[[
                        'text' => __('menu'),
                        'callback_data' => '/start',
                    ]]];
                }
            } else {
                $order = fn_get_order_short_info($id);
                $statuses = fn_get_simple_statuses();
                $return['message'] = __('order') . ' #' . $id . '. ' . __('status') . ': ' . $statuses[$order['status']] . "\r\n";
                foreach ($statuses as $code => $descr) {
                    if ($code == $order['status']) continue;
                    $buttons[] = [
                        'text' => $descr,
                        'callback_data' => '/order_status/'.$id.'/?status='.$code,
                    ];
                }
                $return['inline_keyboard'] = array_chunk($buttons, 2);
            }
            return $return;
        } else {

        }
    }

    public function privileges($id, $params, $context)
    {
        $rivileges = parent::privileges($id, $params, $context);
        $rivileges['backend'] = true;
        return $rivileges;
    }
}
