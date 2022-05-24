<?php

namespace Tygh\Models;

use Tygh\Models\Company;

class Vendor extends Company
{
    public function getFields($params)
    {
        $fields = parent::getFields($params);
        $fields[] = 'p.usergroup_ids';
        return $fields;
    }

    public function gatherAdditionalItemsData(&$items, $params)
    {
        parent::gatherAdditionalItemsData($items, $params);
        foreach ($items as $key => $item) {
            $items[$key]['usergroup_ids'] = !empty($item['usergroup_ids']) ? explode(',', $item['usergroup_ids']) : array();
        }
    }
}
