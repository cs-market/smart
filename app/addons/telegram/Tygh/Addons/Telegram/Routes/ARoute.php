<?php

namespace Tygh\Addons\Telegram\Routes;

abstract class ARoute
{
    protected $auth = [];
    protected $area;
    protected $chat_id;

    /**
     * Object constructor
     *
     * @param array $config configuration options
     */
    public function __construct($auth, $area, $chat_id)
    {
        $this->auth = $auth;
        $this->area = $area;
        $this->chat_id = $chat_id;
    }

    public function render($id, $params, $context) {

    }

    public function isAccessable($id = false, $params = false, $context = false)
    {
        $privileges = $this->privileges($id, $params, $context);

        if (is_array($privileges)) {
            $type = 'anonymous';
            if ($this->area == 'A') {
                $type = 'backend';
            } elseif (!empty($this->auth['user_id'])) {
                $type = 'frontend';
            }
            $privileges = $privileges[$type];
        }

        return $privileges ?? false;
    }

    public function privileges($id, $params, $context)
    {
        return [
            'frontend' => false,
            'backend' => false,
            'anonymous' => false,
        ];
    }

    protected static function generatePagination($search, $entity = '/products') {
        $pagination = fn_generate_pagination($search);
        $pagination_keyboard_row = [];
        if ($pagination['prev_page'] && $pagination['prev_page'] != $pagination['current_page']) {
            $pagination_keyboard_row[] = [
                'text' => __('page') . ' ' . $pagination['prev_page'],
                'callback_data' => $entity . '/?page='.$pagination['prev_page'],
            ];
        }

        if ($pagination['next_page'] != $pagination['current_page']) {
            $pagination_keyboard_row[] = [
                'text' => __('page') . ' ' . $pagination['next_page'],
                'callback_data' => $entity . '/?page='.$pagination['next_page'],
            ];
        }

        return $pagination_keyboard_row;
    }
}
