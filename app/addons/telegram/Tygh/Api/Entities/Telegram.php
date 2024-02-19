<?php

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;

/**
 * Class Telegram implements API entity of telegram bot.
 *
 * @package Tygh\Api\Entities
 */
class Telegram extends AEntity
{
    protected $render_manager;

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

    public function create($params) {
        $status = Response::STATUS_OK;
        $data = '';

        $command = fn_dot_syntax_get('callback_query.data', $params, fn_dot_syntax_get('message.text', $params, false));
        $chat_id = fn_dot_syntax_get('callback_query.message.chat.id', $params, fn_dot_syntax_get('message.chat.id', $params, false));

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
