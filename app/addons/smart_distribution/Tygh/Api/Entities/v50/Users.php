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
}
