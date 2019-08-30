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


use Tygh\Addons\StorefrontRestApi\ASraEntity;
use Tygh\Api\Response;
use Tygh\BlockManager\Location;


/**
 * Class SraBmLocations
 *
 * @package Tygh\Api\Entities
 */
class SraBmLocations extends ASraEntity
{
    /**
     * @inheritDoc
     */
    public function index($id = '', $params = array())
    {
        $status = Response::STATUS_OK;
        $layout_id = 0;

        if ($this->getParentName() === 'sra_bm_layouts') {
            $layout = $this->getParentData();
            $layout_id = $layout['layout_id'];
        }

        if ($id) {
            if (is_numeric($id)) {
                $data = Location::instance($layout_id)->getById($id);
            } else {
                $data = Location::instance($layout_id)->getList(array(
                    'dispatch' => $id,
                    'sort_by' => 'object_ids',
                    'sort_order' => 'desc',
                    'limit' => 1
                ));

                if (!empty($data)) {
                    $data = reset($data);
                }
            }

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            }
        } else {
            $data = Location::instance($layout_id)->getList($params);
        }

        return array(
            'status' => $status,
            'data'   => $data,
        );
    }

    /**
     * @inheritDoc
     */
    public function create($params)
    {
        if ($this->getParentName() !== 'sra_bm_layouts') {
            return array(
                'status' => Response::STATUS_BAD_REQUEST
            );
        }

        $data = array();
        $status = Response::STATUS_BAD_REQUEST;
        $layout = $this->getParentData();
        $layout_id = $layout['layout_id'];

        if (empty($params['dispatch'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'dispatch'
            ));
        } elseif (empty($params['name'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'name'
            ));
        } else {
            $location_id = Location::instance($layout_id)->update($params);

            if ($location_id) {
                $status = Response::STATUS_OK;
                $data = array('location_id' => $location_id);
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    /**
     * @inheritDoc
     */
    public function update($id, $params)
    {
        return array(
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        );
    }

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
        return array(
            'status' => Response::STATUS_METHOD_NOT_ALLOWED,
        );
    }

    /**
     * @inheritDoc
     */
    public function isValidIdentifier($id)
    {
        return !empty($id);
    }

    /**
     * @inheritDoc
     */
    public function privilegesCustomer()
    {
        return array(
            'index'  => true,
            'create' => false,
            'update' => false,
            'delete' => false,
        );
    }

    /**
     * @inheritDoc
     */
    public function privileges()
    {
        return array(
            'index'  => true,
            'create' => 'edit_blocks',
            'update' => 'edit_blocks',
            'delete' => 'edit_blocks',
        );
    }
}