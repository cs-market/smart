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

if ($mode == 'view') {
	$products = Tygh::$app['view']->getTemplateVars('products');
	$selected_layout = Tygh::$app['view']->getTemplateVars('selected_layout');
	if ($selected_layout != 'short_list') {
		fn_gather_additional_products_data($products, array(
			'get_stickers_for' => 'C'
		));
		Tygh::$app['view']->assign('products', $products);
	}
}