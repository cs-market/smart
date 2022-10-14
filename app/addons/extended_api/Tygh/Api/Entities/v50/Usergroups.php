<?php

namespace Tygh\Api\Entities\v50;

use Tygh\Api\Response;
use Tygh\Registry;
use Tygh\Api\Entities\Usergroups as DefaultUsergroups;
use Tygh\Models\Vendor;

class Usergroups extends DefaultUsergroups
{
    public function isAccessable($method_name)
    {
        if ($this->area == 'C') {
            $privileges = $this->privilegesCustomer();
        } else {
            $privileges = $this->privileges();
        }

        $is_accessable = false;
        if (isset($privileges[$method_name])) {
            if (is_bool($privileges[$method_name])) {
                $is_accessable = $privileges[$method_name];
            } else {
                if ($this->auth) {
                    $is_accessable = fn_check_user_access($this->auth['user_id'], $privileges[$method_name]);
                }
            }
        }

        return $is_accessable;
    }

    public function delete($id)
    {
        if (!$this->getParentName() && Registry::get('runtime.company_id')) {
            $company = Vendor::model()->current();
            if (!empty($company) && !empty($company->usergroup_ids)) {
                if (!in_array($id, $company->usergroup_ids)) {
                    return array('data' => [], 'status' => Response::STATUS_BAD_REQUEST);
                }
            }
        }
        return parent::delete($id);
    }

    protected function createOrUpdate($params, $id = null)
    {
        if (Registry::get('runtime.company_id')) {
            $params['type'] = 'C';
            $params['status'] = 'A';
            if ($id) {
                $company = Vendor::model()->current();
                if (!empty($company) && !empty($company->usergroup_ids) && !in_array($id, $company->usergroup_ids)) {
                    return array('data' => [], 'status' => Response::STATUS_BAD_REQUEST);
                }
            }
        }

        return parent::createOrUpdate($params, $id);
    }
}
