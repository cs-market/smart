<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

namespace Tygh\Api\Entities\v50;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Api\Entities\AuthTokens as BaseAuthTokens;

class AuthTokens extends BaseAuthTokens
{
    /**
     * Creates or updates auth token for requests.
     *
     * @param array $params Request
     *
     * @return array Auth status and data. On success data contains auth token and token time-to-live in seconds.
     *               To determine token expiry time, add TTL to current timestamp.
     */
    public function create($params)
    {
        $result = parent::create($params);
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();

        $email = $this->safeGet($params, 'email', '');
        $password = $this->safeGet($params, 'password', '');

        if (!$email) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'email'
            ));
        } elseif (!$password) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'password'
            ));
        } else {
            $status = Response::STATUS_NOT_FOUND;

            list($user_exists, $user_data, $login, $password, $salt) = fn_auth_routines(
                array(
                    'user_login' => $email,
                    'password'   => $password,
                ),
                array()
            );

            if ($user_data && fn_user_password_verify((int) $user_data['user_id'], $password, (string) $user_data['password'], $salt)) {
                \Tygh::$app['session']->regenerateID();
                list($token, $expiry_time) = fn_get_fresh_user_auth_token($user_data['user_id'], SESSION_ALIVE_TIME);

                $status = Response::STATUS_CREATED;
                $data = array(
                    'token' => $token,
                    'ttl'   => $expiry_time - TIME,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }
}
