<?php

namespace Tygh\Addons\Telegram\Routes;

class Auth extends ARoute {
    public function render($id, $params, $context) {
        if (empty($this->auth['user_id']) && !empty($id)) {
            $key_data = reset(fn_get_ekeys([
                'ekey' => $id
            ]));
            fn_delete_ekey($ekey);

            if (empty($key_data) || $key_data['ttl'] < TIME) {
                return ['message' => 'Время авторизации истекло. Попробуйте заново.'];
            } else {
                if ($key_data['data']['object_type'] == 'user') {
                    db_query('UPDATE ?:users SET chat_id = ?s WHERE user_id = ?i', $this->chat_id, $key_data['data']['object_id']);
                    $user_data = fn_get_user_short_info($key_data['data']['object_id']);
                }

                return [
                    'message' => __('hello') . ', ' . $user_data['firstname'],
                    'inline_keyboard' => [[
                    [
                        'text' => 'В меню',
                        'callback_data' => '/start'
                    ]
                ]]];
            }
        }

        list($request['user_login'], $request['password']) = array_map('trim', explode(' ', $context['message']['text']));

        if (!empty($request['user_login']) && !empty($request['password'])) {
            list($user_exists, $user_data, $login, $password, $salt) = fn_auth_routines($request, array());

            if ($user_data && fn_user_password_verify((int) $user_data['user_id'], $password, (string) $user_data['password'], $salt)) {
                db_query('UPDATE ?:users SET chat_id = ?s WHERE user_id = ?i', $this->chat_id, $user_data['user_id']);
                return [
                    'message' => __('hello') . ', ' . $user_data['firstname'],
                    'inline_keyboard' => [[
                    [
                        'text' => 'В меню',
                        'callback_data' => '/start'
                    ]
                ]]];
            } else {
                fn_set_storage_data('telegram_last_command_'.$this->chat_id, 'Auth');
                return [
                    'message' => 'Неправильный логин/пароль. Попробуйте заново',
                    'force_reply' => true,
                    'input_field_placeholder' => 'login password'
                ];
            }
        } else {
            fn_set_storage_data('telegram_last_command_'.$this->chat_id, 'Auth');
            return [
                'message' => 'Введите логин и пароль через пробел',
                'force_reply' => true,
                'input_field_placeholder' => 'login password'
            ];
        }
    }

    public function privileges($id, $params, $context)
    {
        $privileges = parent::privileges($id, $params, $context);
        $privileges['anonymous'] = true;

        return $privileges;
    }
}
