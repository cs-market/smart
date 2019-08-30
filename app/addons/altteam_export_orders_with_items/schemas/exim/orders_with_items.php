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

use Tygh\Registry;

include_once(Registry::get('config.dir.schemas') . 'exim/orders.functions.php');
include_once(Registry::get('config.dir.schemas') . 'exim/order_items.functions.php');

include_once(Registry::get('config.dir.addons') . '/altteam_export_orders_with_items/schemas/exim/orders_with_items.functions.php');

$schema = array(
    'section' => 'orders',
    'pattern_id' => 'orders_with_items',
    'name' => __('orders_with_items'),
    'key' => array('order_id'),
    'order' => 0,
    'table' => 'orders',
    'export_only' => true,
    'permissions' => array(
        'import' => 'edit_order',
        'export' => 'view_orders',
    ),
    'references' => array(
        'order_details' => array(
            'reference_fields' => array('order_id' => '&order_id'),
            'join_type' => 'LEFT',
            'alt_key' => array('order_id'),
            'import_skip_db_processing' => true
        ),
    ),    
    'condition' => array(
        'conditions' => array('is_parent_order' => 'N'),
        'use_company_condition' => true,
    ),
    'range_options' => array(
        'selector_url' => 'orders.manage',
        'object_name' => __('orders'),
    ),
    'export_fields' => array(
        'Order ID' => array(
            'db_field' => 'order_id',
            'alt_key' => true,
            'required' => true,
            'process_get' => array('fn_exim_orders_with_items_get', 'Order ID', '#key', '#this')
        ),
        'E-mail' => array(
            'db_field' => 'email',
            'required' => true,
            'process_get' => array('fn_exim_orders_with_items_get', 'E-mail', '#key', '#this')            
        ),
        'User ID' => array(
            'db_field' => 'user_id',
            'process_get' => array('fn_exim_orders_with_items_get', 'User ID', '#key', '#this')
        ),
        'Issuer ID' => array(
            'db_field' => 'issuer_id',
            'process_get' => array('fn_exim_orders_with_items_get', 'Issuer ID', '#key', '#this')            
        ),
        'Total' => array(
            'db_field' => 'total',
            'process_get' => array('fn_exim_orders_with_items_get', 'Total', '#key', '#this')
        ),
        'Subtotal' => array(
            'db_field' => 'subtotal',
            'process_get' => array('fn_exim_orders_with_items_get', 'Subtotal', '#key', '#this')
        ),
        'Discount' => array(
            'db_field' => 'discount',
            'process_get' => array('fn_exim_orders_with_items_get', 'Discount', '#key', '#this')
        ),
        'Payment surcharge' => array(
            'db_field' => 'payment_surcharge',
            'process_get' => array('fn_exim_orders_with_items_get', 'Payment surcharge', '#key', '#this')
        ),
        'Shipping cost' => array(
            'db_field' => 'shipping_cost',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping cost', '#key', '#this')
        ),
        'Date' => array(
            'db_field' => 'timestamp',
            'process_get' => array('fn_orders_with_items_timestamp_to_date', 'Date', '#key', '#this'),
        ),
        'Status' => array(
            'db_field' => 'status',
            'process_get' => array('fn_exim_orders_with_items_get', 'Status', '#key', '#this'),            
        ),
        'Notes' => array(
            'db_field' => 'notes',
            'process_get' => array('fn_exim_orders_with_items_get', 'Notes', '#key', '#this'),            
        ),
        'Payment ID' => array(
            'db_field' => 'payment_id',
            'process_get' => array('fn_exim_orders_with_items_get', 'Payment ID', '#key', '#this'),            
        ),
        'IP address' => array(
            'db_field' => 'ip_address',
            'process_get' => array('fn_orders_with_items_ip_from_db', 'IP address', '#key', '#this'),
        ),
        'Details' => array(
            'db_field' => 'details',
            'process_get' => array('fn_exim_orders_with_items_get', 'Details', '#key', '#this'),            
        ),
        'Payment information' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_data', 'Payment information', '#key', 'P'),
        ),
        'Taxes' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_data', 'Taxes', '#key', 'T'),
        ),
        'Coupons' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_data', 'Coupons', '#key', 'C'),
        ),
        'Shipping' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_data', 'Shipping', '#key', 'L'),
        ),
        'Invoice ID' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_docs', 'Invoice ID', '#key', 'I'),
        ),
        'Credit memo ID' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_docs', 'Credit memo ID', '#key', 'C'),
        ),
        'First name' => array(
            'db_field' => 'firstname',
            'process_get' => array('fn_exim_orders_with_items_get', 'First name', '#key', '#this'),            
        ),
        'Last name' => array(
            'db_field' => 'lastname',
            'process_get' => array('fn_exim_orders_with_items_get', 'Last name', '#key', '#this'),            
        ),
        'Company' => array(
            'db_field' => 'company',
            'process_get' => array('fn_exim_orders_with_items_get', 'Company', '#key', '#this'),            
        ),
        'Fax' => array(
            'db_field' => 'fax',
            'process_get' => array('fn_exim_orders_with_items_get', 'Fax', '#key', '#this'),            
        ),
        'Phone' => array(
            'db_field' => 'phone',
            'process_get' => array('fn_exim_orders_with_items_get', 'Phone', '#key', '#this'),            
        ),
        'Web site' => array(
            'db_field' => 'url',
            'process_get' => array('fn_exim_orders_with_items_get', 'Web site', '#key', '#this'),            
        ),
        'Tax exempt' => array(
            'db_field' => 'tax_exempt',
            'process_get' => array('fn_exim_orders_with_items_get', 'Tax exempt', '#key', '#this'),            
        ),
        'Language' => array(
            'db_field' => 'lang_code',
            'process_get' => array('fn_exim_orders_with_items_get', 'Language', '#key', '#this'),            
        ),
        'Billing: first name' => array(
            'db_field' => 'b_firstname',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: first name', '#key', '#this'),            
        ),
        'Billing: last name' => array(
            'db_field' => 'b_lastname',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: last name', '#key', '#this'),            
        ),
        'Billing: address' => array(
            'db_field' => 'b_address',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: address', '#key', '#this'),            
        ),
        'Billing: address (line 2)' => array(
            'db_field' => 'b_address_2',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: address (line 2)', '#key', '#this'),            
        ),
        'Billing: city' => array(
            'db_field' => 'b_city',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: city', '#key', '#this'),            
        ),
        'Billing: state' => array(
            'db_field' => 'b_state',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: state', '#key', '#this'),            
        ),
        'Billing: country' => array(
            'db_field' => 'b_country',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: country', '#key', '#this'),            
        ),
        'Billing: zipcode' => array(
            'db_field' => 'b_zipcode',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: zipcode', '#key', '#this'),            
        ),
        'Billing: phone' => array(
            'db_field' => 'b_phone',
            'process_get' => array('fn_exim_orders_with_items_get', 'Billing: phone', '#key', '#this'),            
        ),
        'Shipping: first name' => array(
            'db_field' => 's_firstname',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: first name', '#key', '#this'),            
        ),
        'Shipping: last name' => array(
            'db_field' => 's_lastname',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: last name', '#key', '#this'),            
        ),
        'Shipping: address' => array(
            'db_field' => 's_address',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: address', '#key', '#this'),            
        ),
        'Shipping: address (line 2)' => array(
            'db_field' => 's_address_2',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: address (line 2)', '#key', '#this'),            
        ),
        'Shipping: city' => array(
            'db_field' => 's_city',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: city', '#key', '#this'),            
        ),
        'Shipping: state' => array(
            'db_field' => 's_state',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: state', '#key', '#this'),            
        ),
        'Shipping: country' => array(
            'db_field' => 's_country',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: country', '#key', '#this'),            
        ),
        'Shipping: zipcode' => array(
            'db_field' => 's_zipcode',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: zipcode', '#key', '#this'),            
        ),
        'Shipping: phone' => array(
            'db_field' => 's_phone',
            'process_get' => array('fn_exim_orders_with_items_get', 'Shipping: phone', '#key', '#this'),            
        ),
        'Extra fields' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_with_items_get_extra_fields', 'Extra fields', '#key', '#lang_code'),
        ),

        // Order ITEMS
        'Item ID' => array(
            'table' => 'order_details',
            'db_field' => 'item_id',
            'alt_key' => true,
            'required' => true,
        ),
        'Product ID' => array(
            'table' => 'order_details',
            'db_field' => 'product_id'
        ),
        'Issuer ID' => array(
            'table' => 'order_details',
            'db_field' => 'issuer_id',
        ),        
        'Product code' => array(
            'table' => 'order_details',
            'db_field' => 'product_code'
        ),
        'Price' => array(
            'table' => 'order_details',
            'db_field' => 'price'
        ),
        'Quantity' => array(
            'table' => 'order_details',
            'db_field' => 'amount'
        ),
        'Extra' => array(
            'table' => 'order_details',
            'db_field' => 'extra',
            'linked' => true,
            'process_get' => array('fn_exim_orders_with_items_get_extra', '#this'),
        ),
        'Product' => array(
            'table' => 'order_details',
            'db_field' => 'extra',
            'linked' => true,
            'process_get' => array('fn_exim_orders_with_items_get_product_name', '#this'),
        ),
        'Options' => array(
            'table' => 'order_details',
            'db_field' => 'extra',
            'linked' => true,
            'process_get' => array('fn_exim_orders_with_items_get_option_names', '#this'),
        ),
        'Variants' => array(
            'table' => 'order_details',
            'db_field' => 'extra',
            'linked' => true,
            'process_get' => array('fn_exim_orders_with_items_get_variant_names', '#this'),
        ),

    ),
);

if (Registry::get('addons.altteam_simple_profit.status') == 'A') {
    $schema['export_fields']['Cost price'] = array(
            'table' => 'order_details',
            'db_field' => 'cost_price'
        );
}

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = array(
        'db_field' => 'company_id',
        'process_get' => array('fn_orders_with_items_get_company_name', 'Store', '#key', '#this'),
    );
    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
    }
}

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['export_fields']['Vendor'] = array(
        'db_field' => 'company_id',
        'process_get' => array('fn_orders_with_items_get_company_name', 'Store', '#key', '#this'),
    );

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Vendor']['required'] = true;
    }
}

return $schema;
