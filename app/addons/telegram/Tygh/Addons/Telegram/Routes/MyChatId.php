<?php

namespace Tygh\Addons\Telegram\Routes;

class MyChatId extends ARoute {
    public function render($id, $params, $context) {
        return ['message' => $this->chat_id];
    }

    public function privileges()
    {
        return true;
    }
}
