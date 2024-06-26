<?php
/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*      Copyright (c) 2013 CS-Market Ltd. All rights reserved.             *
*                                                                         *
*  This is commercial software, only users who have purchased a valid     *
*  license and accept to the terms of the License Agreement can install   *
*  and use this program.                                                  *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*  PLEASE READ THE FULL TEXT OF THE SOFTWARE LICENSE AGREEMENT IN THE     *
*  "license agreement.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * **/

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'promotion_import/schemas/exim/promotions.functions.php');

$schema = array(
    'section' => 'promotions',
    'pattern_id' => 'promotions',
    'name' => __('promotions'),
    'key' => array('promotion_id'),
    'table' => 'promotions',
    'permissions' => array(
        'import' => 'manage_promotions',
    ),
    'references' => [
        'promotion_descriptions' => [
            'reference_fields' => ['promotion_id' => '#key', 'lang_code' => '#lang_code'],
            'join_type'        => 'LEFT'
        ],
    ],
    'only_import' => true,
    'export_fields' => array (
        'Promotion code' => [
            'db_field' => 'external_id',
            'required' => true,
            'alt_key' => true,
        ],
        'Company' => array (
            'db_field' => 'company_id',
            'process_get' => array('fn_get_company_name', '#this'),
            'convert_put' => array('fn_get_company_id_by_name', '#this'),
        ),
        'Suffix' => [
            'db_field' => 'suffix',
            'linked' => false,
        ],
        'Name' => [
            'table'       => 'promotion_descriptions',
            'db_field'    => 'name',
            'multilang'   => true,
            // 'process_get' => ['fn_export_product_descr', '#key', '#this', '#lang_code', 'product'],
            // 'process_put' => ['fn_import_product_descr', '#this', '#key', 'product'],
        ],
        'Full description' => [
            'table'       => 'promotion_descriptions',
            'db_field'    => 'detailed_description',
            'multilang'   => true,
        ],
        'Short description' => [
            'table'       => 'promotion_descriptions',
            'db_field'    => 'short_description',
            'multilang'   => true,
        ],
        'Available since' => [
            'db_field'      => 'from_date',
            'convert_put'   => ['fn_promotion_import_put_optional_timestamp', '#this'],
            'return_result' => true
        ],
        'Available till' => [
            'db_field'      => 'to_date',
            'convert_put'   => ['fn_promotion_import_put_optional_timestamp', '#this', 'true'],
            'return_result' => true
        ],
        'Stop other rules' => [
            'db_field'      => 'stop_other_rules',
        ],
        'Priority' => [
            'db_field'      => 'priority',
        ],

        'Status' => array(
            'db_field' => 'status'
        ),
        'Zone' => array(
            'db_field' => 'zone'
        ),
    ),
    'pre_processing' => [
        'glue_primary_field' => [
            'function' => 'fn_promotion_import_glue_primary_field',
            'args' => array('$import_data'),
            'import_only' => true,
        ]
    ],
    'import_process_data' => [
        'build_conditions' => array(
            'function' => 'fn_promotion_import_build_conditions',
            'args' => array('$object', '$primary_object_id', '$processed_data'),
            'import_only' => true,
        ),
        'build_bonuses' => array(
            'function' => 'fn_promotion_import_build_bonuses',
            'args' => array('$object'),
            'import_only' => true,
        ),
        'generate_promotion_hashes' => [
            'function' => 'fn_promotion_import_generate_promotion_hashes',
            'args' => array('$object'),
            'import_only' => true,
        ]
    ],
    'post_processing' => [
        'disabled_promo_notification' => [
            'function' => 'fn_promotion_import_disabled_promo_notification',
            'args' => ['$processed_data'],
            'import_only' => true,
        ]
    ]
);

// $promotion_schema = fn_get_schema('promotions', 'schema');
// foreach ($promotion_schema['conditions'] as $condition_name => $condition) {
//     foreach ($condition['operators'] as $operator) {
//         $schema['export_fields']['c.'.$condition_name.'.'.$operator] = [
//             'db_field' => 'c.'.$condition_name.'.'.$operator,
//             'linked' => false,
//         ];
//     }
// }

// foreach ($promotion_schema['bonuses'] as $bonus_name => $bonus) {
//     foreach ($bonus['discount_bonuses'] as $discount_bonus) {
//         $schema['export_fields']['b.'.$bonus_name.'.'.$discount_bonus] = [
//             'db_field' => 'b.'.$bonus_name.'.'.$discount_bonus,
//             'linked' => false,
//         ];
//     }
// }

if (fn_allowed_for('MULTIVENDOR')) {
    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Company']['required'] = true;
    } else {
        $schema['import_get_primary_object_id']['get_primary_keys'] = [
            'function' => 'fn_promotion_import_fill_company_id',
            'args' => array('$alt_keys', '$object'),
            'import_only' => true,
        ];
    }
    $schema['export_fields']['Company']['alt_key'] = true;
}

if (Registry::get('addons.product_stickers.status') == 'A') {
    $schema['export_fields']['Stickers'] = array (
        'db_field' => 'sticker_ids',
    );
}

return $schema;
