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

        unset($params['category_id']);

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
        } else {
            $params['company_id'] = Registry::get('runtime.company_id');
        }

        if ($valid_params) {

            $feature_id = fn_update_product_feature($params, 0);

            if ($feature_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'feature_id' => $feature_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }
}
