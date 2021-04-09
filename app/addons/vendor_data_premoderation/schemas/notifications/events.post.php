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

use Tygh\Addons\VendorDataPremoderation\Notifications\DataProviders\PremoderationDataProvider;
use Tygh\Enum\Addons\VendorDataPremoderation\PremoderationStatuses;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\RecipientSearchMethods;
use Tygh\Enum\UserTypes;
use Tygh\Notifications\DataValue;
use Tygh\Notifications\Transports\Internal\InternalTransport;
use Tygh\Notifications\Transports\Mail\MailTransport;
use Tygh\Notifications\Transports\Mail\MailMessageSchema;
use Tygh\Notifications\Transports\Internal\InternalMessageSchema;
use Tygh\NotificationsCenter\NotificationsCenter;

defined('BOOTSTRAP') or die('Access denied');

$schema['vendor_data_premoderation.product_status.approved'] = [
    'group'     => 'vendor_data_premoderation',
    'name'      => [
        'template' => 'vendor_data_premoderation.event.product_status.approved.name',
        'params'   => [],
    ],
    'data_provider' => [PremoderationDataProvider::class, 'factory'],
    'receivers' => [
        UserTypes::VENDOR => [
            MailTransport::getId()     => MailMessageSchema::create([
                'area'            => 'A',
                'from'            => 'default_company_support_department',
                'to'              => DataValue::create('to', 'company_support_department'),
                'template_code'   => 'vendor_data_premoderation_notification',
                'legacy_template' => 'addons/vendor_data_premoderation/notification.tpl',
                'language_code'   => DataValue::create('lang_code'),
                'data_modifier'   => function (array $data) {
                    $company_placement_info = fn_get_company_placement_info($data['company_id']);

                    return array_merge($data, [
                        'status' => PremoderationStatuses::APPROVED,
                        'to' => isset($company_placement_info['company_support_department'])
                            ? $company_placement_info['company_support_department']
                            : 'company_support_department'
                    ]);
                }
            ]),
            InternalTransport::getId() => InternalMessageSchema::create([
                'tag'                       => 'vendor_data_premoderation',
                'area'                      => 'V',
                'section'                   => NotificationsCenter::SECTION_PRODUCTS,
                'recipient_search_method'   => RecipientSearchMethods::USER_ID,
                'recipient_search_criteria' => DataValue::create('admin_user_id'),
                'language_code'             => DataValue::create('lang_code'),
                'action_url'                => DataValue::create('manage_urn'),
                'severity'                  => NotificationSeverity::NOTICE,
                'template_code'             => 'vendor_data_premoderation_product_approved',
            ]),
        ],
    ],
];

$schema['vendor_data_premoderation.product_status.disapproved'] = [
    'group'     => 'vendor_data_premoderation',
    'name'      => [
        'template' => 'vendor_data_premoderation.event.product_status.disapproved.name',
        'params'   => [],
    ],
    'data_provider' => [PremoderationDataProvider::class, 'factory'],
    'receivers' => [
        UserTypes::VENDOR => [
            MailTransport::getId()     => MailMessageSchema::create([
                'area'            => 'A',
                'from'            => 'default_company_support_department',
                'to'              => DataValue::create('to', 'company_support_department'),
                'template_code'   => 'vendor_data_premoderation_notification',
                'legacy_template' => 'addons/vendor_data_premoderation/notification.tpl',
                'language_code'   => DataValue::create('lang_code'),
                'data_modifier'   => function (array $data) {
                    $company_placement_info = fn_get_company_placement_info($data['company_id']);

                    return array_merge($data, [
                        'status' => PremoderationStatuses::DISAPPROVED,
                        'to' => isset($company_placement_info['company_support_department'])
                            ? $company_placement_info['company_support_department']
                            : 'company_support_department'
                    ]);
                }
            ]),
            InternalTransport::getId() => InternalMessageSchema::create([
                'tag'                       => 'vendor_data_premoderation',
                'area'                      => 'V',
                'section'                   => NotificationsCenter::SECTION_PRODUCTS,
                'recipient_search_method'   => RecipientSearchMethods::USER_ID,
                'recipient_search_criteria' => DataValue::create('admin_user_id'),
                'language_code'             => DataValue::create('lang_code'),
                'action_url'                => DataValue::create('manage_urn'),
                'severity'                  => NotificationSeverity::NOTICE,
                'template_code'             => 'vendor_data_premoderation_product_disapproved',
            ]),
        ],
    ],
];

return $schema;
