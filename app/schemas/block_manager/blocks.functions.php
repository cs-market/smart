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

function fn_blocks_get_vendor_info()
{
    $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : null;

    $company_data = fn_get_company_data($company_id);
    $company_data['logos'] = fn_get_logos($company_id);

    return $company_data;
}

/**
 * Decides whether to disable cache for "products" block.
 *
 * @param $block_data
 *
 * @return bool Whether to disable cache
 */
function fn_block_products_disable_cache($block_data)
{
    // Disable cache for "Recently viewed" filling
    if (isset($block_data['content']['items']['filling'])
        && $block_data['content']['items']['filling'] == 'recent_products'
    ) {
        return true;
    }

    return false;
}

/**
 * Gets the data of companies by parameters.
 *
 * @param array $params An array of search parameters.
 *
 * @return array An array of companies
 */
function fn_blocks_get_vendors($params = array())
{
    $params['company_id'] = empty($params['item_ids']) ? array() : fn_explode(',', $params['item_ids']);

    $params['extend'] = array(
        'products_count' => empty($params['block_data']['properties']['show_products_count']) ? 'N' : $params['block_data']['properties']['show_products_count'],
        'logos'          => true,
        'placement_info' => true,
    );

    $displayed_vendors = empty($params['block_data']['properties']['displayed_vendors']) ? 0 : $params['block_data']['properties']['displayed_vendors'];

    list($companies,) = fn_get_companies($params, Tygh::$app['session']['auth'], $displayed_vendors);

    if ($companies) {
        $companies = fn_array_combine(fn_array_column($companies, 'company_id'), $companies);
    }

    return array($companies);
}