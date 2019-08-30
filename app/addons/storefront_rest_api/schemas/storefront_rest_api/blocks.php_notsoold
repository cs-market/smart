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

$schema = array(
    'categories' => array(
        'content' => array(
            'items' => array(
                'fillings' => array(
                    'manually' => array(
                        'params' => array(
                            'get_images' => true,
                        ),
                    ),
                    'newest' => array(
                        'params' => array(
                            'get_images' => true,
                        ),
                    ),
                    'full_tree_cat' => array(
                        'params' => array(
                            'get_images' => true,
                        ),
                    ),
                    'subcategories_tree_cat' => array(
                        'params' => array(
                            'get_images' => true,
                        ),
                    ),
                ),
            ),
        ),
    ),
    'products' => array(
        'content' => array(
            'items' => array(
                'post_function' => function ($products) {
                    return fn_storefront_rest_api_format_products_prices($products);
                }
            )
        )
    )
);

return $schema;
