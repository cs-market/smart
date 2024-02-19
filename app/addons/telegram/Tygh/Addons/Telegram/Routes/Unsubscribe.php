<?php

namespace Tygh\Addons\Telegram\Routes;

class Unsubscribe extends ARoute {
    public function render($id, $params, $context) {
        db_query('UPDATE ?:users SET chat_id = ?s WHERE user_id = ?i', "", $this->auth['user_id']);
        return ['message' => __('successful')];
    }

    public function privileges($id, $params, $context) {
        $privileges = [
            'frontend' => !empty($this->auth['user_id']),
            'backend' => !empty($this->auth['user_id']),
            'anonymous' => false,
        ];

        return $privileges;
    }
}
