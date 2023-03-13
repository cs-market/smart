<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Response;
use Tygh\Registry;
use Tygh\Api\Entities\Features as BaseFeatures;

class Features extends BaseFeatures
{
    public function create($params)
    {
        $status = Response::STATUS_BAD_REQUEST;
        $data = array();
        $valid_params = true;

        if (!Registry::get('runtime.company_id')) {
            unset($params['category_id']);
        }

        if (empty($params['feature_type'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'feature_type'
            ));
            $valid_params = false;
        }

        if (empty($params['description'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'description'
            ));
            $valid_params = false;
        }

        if (fn_allowed_for('ULTIMATE')) {
            if ((empty($params['company_id'])) && Registry::get('runtime.company_id') == 0) {
                $data['message'] = __('api_need_store');
                $valid_params = false;
            }
        } elseif (Registry::get('runtime.company_id')) {
            $params['company_id'] = Registry::get('runtime.company_id');
        }

        if ($valid_params) {

            $feature_id = fn_update_product_feature($params, 0);

            if ($feature_id) {
                $status = Response::STATUS_CREATED;
                $data = fn_get_product_feature_data($feature_id, true);
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params) {
        $response = parent::update($id, $params);
        if ($response['status'] == Response::STATUS_OK) {
            $feature_id = $response['data']['feature_id'];
            $response['data'] = fn_get_product_feature_data($feature_id, true);
            
        }
        return $response;
    }
}
