<?php 

use Tygh\Registry;

$addon = Registry::get('addons.monolith');

$schema = array(
    'extdata' => array (
        '@attributes' => array (
            'user' => $addon['gate_code'],
        ),
        'scheme' => array (
            '@attributes' => array (
                'name' => 'CRMOrderSD',
                'request' => 'set'
            ),
            'data' => array (
                's' => array (
                    'd' => array (
                        '0' => array (
                            '@attributes' => array (
                                'name' => 'CRMOrderParam',
                            ),
                            'f' => array (
                                '0' => array (
                                    '@attributes' => array (
                                        'name' => 'WorkDate',
                                        'type' => 'Date',
                                    ),
                                ),
                               '1' => array (
                                    '@attributes' => array (
                                        'name' => 'SkipDelete',
                                        'type' => 'Integer',
                                    ),
                                ),
                                '2' => array (
                                    '@attributes' => array (
                                        'name' => 'DeleteWHIncludedOnly',
                                        'type' => 'Integer',
                                    ),
                                ),
                                '3' => array (
                                    '@attributes' => array (
                                        'name' => 'WorkPeriodEnd',
                                        'type' => 'Date',
                                    ),
                                ),
                            ),
                        ),
                        '1' => array (
                            '@attributes' => array (
                                'name' => 'CRMOrder',
                            ),
                            'f' => array (
                                '0' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMOrderNumber',
                                        'type' => 'String',
                                    ),
                                ),
                                '1' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMOrderDate',
                                        'type' => 'Date',
                                    ),
                                ),
                                '2' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMClientId',
                                        'type' => 'String',
                                    ),
                                ),
                                // '2' => array (
                                //     '@attributes' => array (
                                //         'name' => 'CompanyId',
                                //         'type' => 'String',
                                //     ),
                                // ),
                                // '3' => array (
                                //     '@attributes' => array (
                                //         'name' => 'AddressId',
                                //         'type' => 'String',
                                //     ),
                                // ),
                                '4' => array (
                                    '@attributes' => array (
                                        'name' => 'WareHouseId',
                                        'type' => 'String',
                                    ),
                                ),
                                '5' => array (
                                    '@attributes' => array (
                                        'name' => 'PersonId',
                                        'type' => 'String',
                                    ),
                                ),
                                '6' => array (
                                    '@attributes' => array (
                                        'name' => 'ActionDate',
                                        'type' => 'Date',
                                    ),
                                ),
                                '7' => array (
                                    '@attributes' => array (
                                        'name' => 'DocumentTypeId',
                                        'type' => 'String',
                                    ),
                                ),
                                '8' => array (
                                    '@attributes' => array (
                                        'name' => 'StatusId',
                                        'type' => 'String',
                                    ),
                                ),
                            ),
                        ),
                        '2' => array (
                            '@attributes' => array (
                                'name' => 'CRMOrderLine',
                            ),
                            'f' => array (
                                '0' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMOrderNumber',
                                        'type' => 'String',
                                    ),
                                ),
                                '1' => array (
                                    '@attributes' => array (
                                        'name' => 'LineNumber',
                                        'type' => 'Integer',
                                    ),
                                ),
                                // '2' => array (
                                //     '@attributes' => array (
                                //         'name' => 'CRMWareHouseId',
                                //         'type' => 'String',
                                //     ),
                                // ),
                                // '3' => array (
                                //     '@attributes' => array (
                                //         'name' => 'WareHouseId',
                                //         'type' => 'String',
                                //     ),
                                // ),
                                '4' => array (
                                    '@attributes' => array (
                                        'name' => 'WareId',
                                        'type' => 'String',
                                    ),
                                ),
                                '5' => array (
                                    '@attributes' => array (
                                        'name' => 'UnitId',
                                        'type' => 'String',
                                    ),
                                ),
                                '6' => array (
                                    '@attributes' => array (
                                        'name' => 'Price',
                                        'type' => 'Currency',
                                    ),
                                ),
                                '7' => array (
                                    '@attributes' => array (
                                        'name' => 'Quantity',
                                        'type' => 'Currency',
                                    ),
                                ),
                            ),
                        ),
                        '3' => array (
                            '@attributes' => array (
                                'name' => 'CRMOrderOption',
                            ),
                            'f' => array (
                                '0' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMOrderNumber',
                                        'type' => 'String',
                                    ),
                                ),
                                '1' => array (
                                    '@attributes' => array (
                                        'name' => 'OptionTypeId',
                                        'type' => 'String',
                                    ),
                                ),
                                '2' => array (
                                    '@attributes' => array (
                                        'name' => 'Value',
                                        'type' => 'String',
                                    ),
                                ),
                            ),
                        ),
                        '4' => array (
                            '@attributes' => array (
                                'name' => 'CRMOrderDiscountLine',
                            ),
                            'f' => array (
                                '0' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMOrderDate',
                                        'type' => 'Date',
                                    ),
                                ),
                                '1' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMOrderNumber',
                                        'type' => 'String',
                                    ),
                                ),
                                '2' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMPromoActionId',
                                        'type' => 'String',
                                    ),
                                ),
                                '3' => array (
                                    '@attributes' => array (
                                        'name' => 'CRMPromoActionName',
                                        'type' => 'String',
                                    ),
                                ),
                                '4' => array (
                                    '@attributes' => array (
                                        'name' => 'DiscountValue',
                                        'type' => 'Decimal',
                                    ),
                                ),
                                '5' => array (
                                    '@attributes' => array (
                                        'name' => 'DiscountUnit',
                                        'type' => 'String',
                                    ),
                                ),
                                '6' => array (
                                    '@attributes' => array (
                                        'name' => 'DiscountPrice',
                                        'type' => 'Decimal',
                                    ),
                                ),
                                '7' => array (
                                    '@attributes' => array (
                                        'name' => 'WareId',
                                        'type' => 'String',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'o' => array (

                )
            )
        )
    )
);

return $schema;
