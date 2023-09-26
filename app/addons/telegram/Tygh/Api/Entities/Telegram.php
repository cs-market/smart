<?php

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
/**
 * Class SraProducts implements API entity to provide products data.
 *
 * @package Tygh\Api\Entities
 */
class Telegram extends AEntity
{
    protected $render_manager;
//     protected $icon_size_small = [500, 500];
//     protected $icon_size_big = [1000, 1000];
//

    /** @inheritdoc */
    public function __construct($auth = array(), $area = '')
    {
        $area = 'C';
        // when authenticating a user with auth token, force area to customer
        if (!empty($auth) && !$auth['is_token_auth']) {
            $area = $auth['user_type'];
        }
        $this->area = $area;
        parent::__construct($auth, $area);
        $this->render_manager = \Tygh::$app['addons.telegram.render_manager'];
    }

    protected function safeGet($array, $key, $default)
    {
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $length = sizeof($parts);
        } else {
            $parts = (array) $key;
            $length = 1;
        }

        $piece = &$array;

        foreach ($parts as $i => $part) {
            if (!is_array($piece) || !array_key_exists($part, $piece)) {
                return $default;
            }
            $piece = & $piece[$part];
        }

        return $piece;
    }

    public function create($params) {
        $status = Response::STATUS_OK;
        $data = '';
        $command = $this->safeGet($params, 'callback_query.data', $this->safeGet($params, 'message.text', false));
        $chat_id = $this->safeGet($params, 'callback_query.message.chat.id', $this->safeGet($params, 'message.chat.id', false));

        $this->render_manager->initRender($this->auth, $this->area, $chat_id);

        $data = $this->render_manager->renderLocation($command, $params);
        if (!empty($data)) {
            $messenger = \Tygh::$app['addons.telegram.messenger'];
            $result = $messenger->sendMessage($chat_id, $data);

            if ($result->isSuccess()) {
                $status = Response::STATUS_OK;
                $data = 'OK';
            } else {
                $data = $result->getFirstError();
            }
        } else {
            $data = 'message can not be empty';
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }


    public function index($id = '', $params = array()) {

    }

    public function update($id, $params) {

    }
    
    public function delete($id) {

    }
}
