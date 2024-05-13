<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Response;
use Tygh\Api\Entities\Products as BaseProducts;

/**
 * Class SraProducts
 *
 * @package Tygh\Api\Entities
 */
class Products extends BaseProducts
{

    public function create($params)
    {
        $data = array();
        $valid_params = true;
        $status = Response::STATUS_BAD_REQUEST;
        unset($params['product_id']);

        if (empty($params['category_ids'])) {
            $default_category_id = fn_get_or_create_default_category_id(fn_get_runtime_company_id());
            if ($default_category_id) {
                $params['category_ids'] = $default_category_id;
            } else {
                $data['message'] = __('api_required_field', [
                    '[field]' => 'category_ids'
                ]);
                $valid_params = false;
            }
        }

        if (!isset($params['price']) && !isset($params['prices'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'price or prices'
            ));
            $valid_params = false;
        }

        if ($valid_params) {

            if (!is_array($params['category_ids'])) {
                $params['category_ids'] = fn_explode(',', $params['category_ids']);
            }

            $this->prepareFeature($params);
            $this->prepareImages($params);
            $product_id = fn_update_product($params);

            if ($product_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'product_id' => $product_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }
}
