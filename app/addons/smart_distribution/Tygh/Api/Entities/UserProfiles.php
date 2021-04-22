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

        if (!empty($id)) {
            $data = $this->getUserProfiles($id);
            if ($data) {
                $status = Response::STATUS_OK;
            } else {
                $status = Response::STATUS_NOT_FOUND;
            }

        } else {
	    $status = Response::STATUS_NOT_FOUND;
        }

        return array(
            'status' => $status,
            'data' => array_values($data)
        );
    }

    public function create($params)
    {
//         $valid_params = true;
// 
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;
// 
//         if (empty($params['phone']) && empty($params['email'])) {
//             $data['message'] = __('api_required_field', array('[field]' => 'phone'));
//         } else {
// 
//             $params['company_id'] = $this->getCompanyId($params);
//             $request_id = fn_update_call_request($params);
// 
//             if ($request_id) {
//                 $status = Response::STATUS_CREATED;
//                 $data = array(
//                     'request_id' => $request_id,
//                 );
//             }
// 
//         }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;
// 
//         if ($this->getCallRequest($id)) {
//             unset($params['company_id']);
// 
//             if (fn_update_call_request($params, $id)) {
//                 $status = Response::STATUS_OK;
//                 $data = array(
//                     'request_id' => $id
//                 );
//             }
// 
//         }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $status = Response::STATUS_NOT_FOUND;
        $data = array();
// 
//         if ($this->getCallRequest($id) && fn_delete_call_request($id)) {
//             $status = Response::STATUS_NO_CONTENT;
//         }
// 
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

//     protected function getCompanyId($params = array())
//     {
//         if (Registry::get('runtime.simple_ultimate')) {
//             $company_id = Registry::get('runtime.forced_company_id');
//         } else {
//             $company_id = Registry::get('runtime.company_id');
//         }
// 
//         if (empty($company_id) && !empty($params['company_id'])) {
//             $company_id = $params['company_id'];
//         }
// 
//         return $company_id;
//     }

    protected function getUserProfiles($id)
    {
        $user_profiles = fn_get_user_profiles($id);
        if ($user_profiles) {
// 	    $profile_fields = fn_get_profile_fields('O');
// 	    $profile_fields = fn_array_merge($profile_fields['B'], $profile_fields['S']);
// 	    $profile_fields = fn_array_column($profile_fields, 'field_id', 'field_name');
	    
	    foreach ($user_profiles as &$profile) { 
		    $profile['profile_data'] = db_get_row("SELECT * FROM ?:user_profiles WHERE user_id = ?i AND profile_id = ?i", $id, $profile['profile_id']);
		    $profile['profile_name'] = $profile['profile_name'] . "(".$profile['profile_data']['s_address'].")" ;
		    $profile['s_address'] = $profile['profile_data']['s_address'];

		    $prof_cond = $profile['profile_id'] ? db_quote("OR (object_id = ?i AND object_type = 'P')", $profile['profile_id']) : '';
		    $profile['fields'] = db_get_hash_single_array("SELECT field_id, value FROM ?:profile_fields_data WHERE (object_id = ?i AND object_type = 'U') $prof_cond", array('field_id', 'value'), $id);
// 		    $profile['profile_data'] = array_intersect_key($profile['profile_data'], $profile_fields);
	    }
	    
            return $user_profiles;
        }

        return false;
    }

}
