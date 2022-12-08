<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Response;
use Tygh\Registry;
use Tygh\Api\Entities\v20\Users as BaseUsers;

class Users extends BaseUsers
{
    /**
     * @inheritdoc
     */
    public function index($id = 0, $params = array())
    {
        if (!empty($id)) {
            $profiles = fn_get_user_profiles($id, ['fetch_fields_values' => true, 'fetch_descriptions' => false]);
            $profile_id = $this->safeGet($params, 'profile_id', key($profiles));
            $data = fn_get_user_info($id, true, $profile_id);
            if ($this->safeGet($params, 'get_profiles', false) == 'true') {
                $data['profiles'] = $profiles;
            }
        //} elseif (!empty($params['user_ids']) && is_array($params['user_ids'])) {
        } else {
            $auth = $this->auth;
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_elements_per_page'));
            list($data, $params) = fn_get_users($params, $auth, $items_per_page);
        }

        if (!$id) {
            $data = array(
                'users' => $data,
                'params' => $params,
            );
        }

        if (!empty($data) || empty($id)) {
            $status = Response::STATUS_OK;
        } else {
            $status = Response::STATUS_NOT_FOUND;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();
        $valid_params = true;

        $auth = $this->auth;

        $params = $this->filterUserData($params);

        $user_id = 0;

        if (empty($params['user_type'])) {
            $params['user_type'] = 'C';
        }

        if (empty($params['user_login'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'user_login'
            ));
            $valid_params = false;
        }

        if (!isset($params['company_id'])) {
            if (!empty($auth['company_id'])) {
                $params['company_id'] = $auth['company_id'];
            } else {
                $data['message'] = __('api_required_field', array(
                    '[field]' => 'company_id'
                ));
                $valid_params = false;
            }
        }

        if ($valid_params) {
            if (!empty($params['password'])) $params['password'] = fn_password_hash($params['password']);
            list($user_id, $profile_id) = fn_update_user($user_id, $params, $auth, false, false);

            if ($user_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'user_id' => $user_id,
                    'profile_id' => $profile_id
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        if (!empty($params['password'])) $params['password'] = fn_password_hash($params['password']);
        if (empty($params['ship_to_another'])) $params['ship_to_another'] = true;
        return parent::update($id, $params);
    }
}
