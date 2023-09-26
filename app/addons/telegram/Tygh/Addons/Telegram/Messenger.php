<?php

namespace Tygh\Addons\Telegram;

use Tygh\Http;
use Tygh\Registry;
use Tygh\Common\OperationResult;

class Messenger {
    protected $tg_url = 'https://api.telegram.org/bot';
    protected $token;

    public function __construct() {
        $this->token = Registry::get('addons.telegram.token');
    }

    public function send($method, $request = []) {
        $operation_result = new OperationResult(false);

        $response = Http::get($this->tg_url.$this->token.'/'.$method, $request);
        if (!empty($response)) {
            $response = json_decode($response, true);
            $operation_result->setData($response);
            if ($response['ok'] == 'true') {
                $operation_result->setSuccess(true);
                if (!empty(trim($response['description'])))$operation_result->addMessage('', trim($response['description']));
            } else {
                $operation_result->addError($response['error_code'], $response['description']);
            }
        } else {
            $operation_result->addError('', 'no response');
        }

        return $operation_result;
    }

    public function sendMessage($chat_id, $message_data = '') {
        $method = 'sendMessage';
        if (is_array($message_data)) {
            $message = $message_data['message'];
            unset($message_data['message']);
        } else {
            $message = $message_data;
        }

        $request = [
            'parse_mode' => 'html',
            'chat_id' => $chat_id
        ];
        if (isset($message_data['photo'])) {
            $method = 'sendPhoto';
            $request['caption'] = $message;
            $request['photo'] = $message_data['photo'];
            unset($message_data['photo']);
        } else {
            $request['text'] = $message;
        }

        if (!empty($message_data)) {
            $message_data = array_merge($message_data,[
                'resize_keyboard' => false,
                'one_time_keyboard' => true,
            ]);

            $request['reply_markup'] = json_encode($message_data);
        }

        return $this->send($method, $request);
    }
}
