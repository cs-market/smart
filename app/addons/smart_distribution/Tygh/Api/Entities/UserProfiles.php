<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class UserProfiles extends AEntity
{
    public function index($id = 0, $params = array())
    {
        $data = array();

        if (empty($id)) {
            $id = $this->auth['user_id'];
        } elseif ($this->auth['user_type'] != 'A' && $this->auth['user_id'] != $id) {
            $id = 0;
        }
        if ($id) $data = $this->getUserProfiles($id);

        return array(
            'status' => empty($data) ? Response::STATUS_NOT_FOUND : Response::STATUS_OK,
            'data' => array_values($data)
        );
    }

    public function create($params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $status = Response::STATUS_NOT_FOUND;
        $data = array();

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'manage_users',
            'update' => 'manage_users',
            'delete' => 'manage_users',
            'index'  => 'view_users'
        );
    }

    public function privilegesCustomer()
    {
        return [
            'index'  => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        ];
    }

    protected function getUserProfiles($id)
    {

        $user_profiles = fn_get_user_profiles($id);
        if ($user_profiles) {

            foreach ($user_profiles as &$profile) {
                $profile['profile_data'] = db_get_row("SELECT * FROM ?:user_profiles WHERE user_id = ?i AND profile_id = ?i", $id, $profile['profile_id']);
                $profile['profile_name'] = $profile['profile_name'] . "(".$profile['profile_data']['s_address'].")" ;
                $profile['s_address'] = $profile['profile_data']['s_address'];

                $prof_cond = $profile['profile_id'] ? db_quote("OR (object_id = ?i AND object_type = 'P')", $profile['profile_id']) : '';
                $profile['fields'] = db_get_hash_single_array("SELECT field_id, value FROM ?:profile_fields_data WHERE (object_id = ?i AND object_type = 'U') $prof_cond", array('field_id', 'value'), $id);
            }

            return $user_profiles;
        }

        return false;
    }
}
