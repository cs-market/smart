<?php

if (fn_allowed_for('MULTIVENDOR')) {
	$schema['import_process_data']['mve_import_check_product_data']['function'] = 'fn_exim_csv_mve_import_check_product_data';
	return $schema;
}