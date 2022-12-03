<?php

use Tygh\Registry;
use Tygh\Addons\AdvancedImport\Exceptions\FileNotFoundException;
use Tygh\Addons\AdvancedImport\Exceptions\ReaderNotFoundException;
use Tygh\Exceptions\PermissionsException;
use Tygh\Enum\Addons\AdvancedImport\ImportStatuses;
use Tygh\Commerceml\SDRusEximCommerceml;
use Tygh\Commerceml\Logs;
use Tygh\Addons\AdvancedImport\ServiceProvider;
use Tygh\Settings;
use Tygh\Languages\Languages;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_auto_exim_install() {
    $setting = Settings::instance()->getSettingDataByName('log_type_exim');

    if (!$setting) {
        $setting = [
            'edition_type' => 'ROOT,ULT:VENDOR',
            'name' => 'log_type_exim',
            'section_id' => 12,
            'section_tab_id' => 0,
            'type' => 'N',
            'value' => '#M#start_import=Y&finish_import=Y',
            'position' => 10,
            'is_global' => 'Y',
            'parent_id' => 0

        ];

        $descriptions = array();
        foreach (Languages::getAll() as $lang_code => $_lang) {
            $descriptions[] = array(
                'object_type' => Settings::SETTING_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => __('privilege_groups.exim')
            );
        }

        $setting_id = Settings::instance()->update($setting, null, $descriptions, true);

        $variant_id_start = Settings::instance()->updateVariant(array(
            'object_id'  => $setting_id,
            'name'       => 'start_import',
            'position'   => 5,
        ));
        $variant_id_finish = Settings::instance()->updateVariant(array(
            'object_id'  => $setting_id,
            'name'       => 'finish_import',
            'position'   => 10,
        ));

        foreach (Languages::getAll() as $lang_code => $_lang) {
            $description = array(
                'object_id' => $variant_id_start,
                'object_type' => Settings::VARIANT_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => 'Start import'
            );
            Settings::instance()->updateDescription($description);

            $description = array(
                'object_id' => $variant_id_finish,
                'object_type' => Settings::VARIANT_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => 'Finish import'
            );
            Settings::instance()->updateDescription($description);
        }
    }
}

function fn_auto_exim_uninstall() {
    $setting = Settings::instance()->getSettingDataByName('log_type_exim');
    if (!$setting) {
        return;
    }

    Settings::instance()->removeById($setting['object_id']);
}

function fn_auto_exim_save_log($type, $action, $data, $user_id, &$content)
{
    if ($type == 'exim') {
        $content = $data;
    }
}

function fn_auto_exim_update_company_pre(&$company_data, $company_id, $lang_code, $can_update) {
    if (isset($company_data['export_statuses']) && !empty($company_data['export_statuses'])) {
        $company_data['export_statuses'] = implode(',', $company_data['export_statuses']);
    }
}

function fn_auto_exim_send_order_notification($order, $edp_data, $force_notification, $notified, $send_order_notification) {
    if (is_callable('fn_save_order_log')) fn_save_order_log($order['order_id'], Tygh::$app['session']['auth']['user_id'], '', 'Инициирована процедура выгрузки в файл', TIME);
    if (db_get_field('SELECT export_orders FROM ?:companies WHERE company_id = ?i AND FIND_IN_SET(?s, export_statuses)', $order['company_id'], $order['status']) == 'C') {
        if (is_callable('fn_save_order_log')) fn_save_order_log($order['order_id'], Tygh::$app['session']['auth']['user_id'], '', 'Выбран csv формат выгрузки в файл', TIME);
        fn_define('DB_LIMIT_SELECT_ROW', 30);
        foreach (array('orders', 'order_items', 'orders_with_items') as $pattern_id) {
            $layout = db_get_row("SELECT ?:exim_layouts.* FROM ?:exim_layouts LEFT JOIN ?:companies ON ?:exim_layouts.name = ?:companies.company WHERE pattern_id = ?s and company_id = ?i", $pattern_id, $order['company_id']);
            if (!empty($layout)) {
                if (is_callable('fn_save_order_log')) fn_save_order_log($order['order_id'], Tygh::$app['session']['auth']['user_id'], '', "Найден паттерн выгрузки $pattern_id", TIME);
                $cid = Registry::get('runtime.company_id');
                $c_data = Registry::get('runtime.company_data');
                Registry::set('runtime.company_id', $order['company_id']);
                Registry::set('runtime.company_data', fn_get_company_data($order['company_id']));
                $layout['cols'] = explode(',', $layout['cols']);
                $pattern = fn_exim_get_pattern_definition($pattern_id, 'export');
                $options = array(
                    'delimiter' => 'S',
                    'output' => 'S',
                    'force_header' => true,
                    'filename' => 'output/' . $pattern_id . '.' . $order['order_id'] . '.csv',
                );
                fn_mkdir(fn_get_files_dir_path().'output/');
                if (is_file(fn_get_files_dir_path().$options['filename'])) {
                    fn_rm(fn_get_files_dir_path().$options['filename']);
                }
                //$pattern['func_save_content_to_file'] = 'fn_exim_csv_put_csv';
                $pattern['condition']['conditions'] = fn_array_merge($pattern['condition']['conditions'], array('order_id' => $order['order_id']));
                ob_start(null, 0, PHP_OUTPUT_HANDLER_REMOVABLE);
                $res = fn_export($pattern, $layout['cols'], $options);
                $file_exists = false;
                if (is_file(fn_get_files_dir_path().$options['filename'])) {
                    $file_exists = true;
                }
                if (is_callable('fn_save_order_log')) fn_save_order_log($order['order_id'], Tygh::$app['session']['auth']['user_id'], '', "Результат работы экспорта: $res, файл существует: $file_exists ".$options['filename'], TIME);
                fn_set_hook('export_order_to_csv', $pattern, $options, $res, $order);
                ob_end_clean();
                Registry::set('runtime.company_id', $cid);
                Registry::set('runtime.company_data', $c_data);
            }
        }
    }
    if (db_get_field('SELECT export_orders FROM ?:companies WHERE company_id = ?i AND FIND_IN_SET(?s, export_statuses)', $order['company_id'], $order['status']) == 'X') {
        if (is_callable('fn_save_order_log')) fn_save_order_log($order['order_id'], Tygh::$app['session']['auth']['user_id'], '', 'Выбран xml формат выгрузки в файл', TIME);
        $company_id = $order['company_id'];
        $order_id = $order['order_id'];
        $path_commerceml = fn_get_files_dir_path($company_id) . 'output/';
        $log = new Logs('', $path_commerceml);
        Registry::set('runtime.company_id', $company_id);

        $exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
        $manual = true;
        //unset($_SESSION['exim_1c']);
        $lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

        $exim_commerceml->import_params['lang_code'] = $lang_code;
        $exim_commerceml->import_params['manual'] = true;
        list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
        $exim_commerceml->company_id = $company_id;

        $begin = "\xEF\xBB\xBF";
        $xml = new \XMLWriter();

        $xml -> openMemory();
        $xml -> startDocument();
        $xml -> startElement($exim_commerceml->cml['commerce_information']);

        $xml = $exim_commerceml->dataOrderToFile($xml, $order, $lang_code);
            
        $xml -> endElement();
        $data = $begin . $xml -> outputMemory();
        fn_put_contents($path_commerceml."order.$order_id.xml", $data);
    }
}

function fn_auto_exim_mve_import_check_product_data(&$v, $primary_object_id, &$options, &$processed_data, &$skip_record)
{
    if (Registry::get('runtime.company_id')) {
        $v['company_id'] = Registry::get('runtime.company_id');
    }

    if (!empty($primary_object_id['product_id'])) {
        $v['product_id'] = $primary_object_id['product_id'];
    } else {
        unset($v['product_id']);
    }

    // Check the category name
    if (!empty($v['Category'])) {
        if (!fn_mve_import_check_exist_category($v['Category'], $options['category_delimiter'], $v['lang_code'])) {
            unset($v['Category']);
        }
    }

    if (!empty($v['Secondary categories']) && !$skip_record) {
        $delimiter = ';';
        $categories = explode($delimiter, $v['Secondary categories']);
        array_walk($categories, 'fn_trim_helper');

        foreach ($categories as $key => $category) {
            if (!fn_mve_import_check_exist_category($category, $options['category_delimiter'], $v['lang_code'])) {
                unset($categories[$key]);
            }
        }

        $v['Secondary categories'] = implode($delimiter . ' ', $categories);
    }

    return true;
}

function fn_auto_exim_find_files($cid) {
    $dir = fn_get_files_dir_path($cid) . 'exim/autoload/';
    $dir_root = Registry::get('config.dir.root');
    fn_set_hook('auto_exim_find_files', $dir, $cid);

    $fs_files = fn_get_dir_contents($dir, false, true, 'csv');
    $files = array();
    $priority = array('products' => 10, 'users' => 20, 'user_price' => 30, 'qty_discounts' => 35, 'orders' => 40);
    foreach ($fs_files as $file) {
        $tmp = '';
        $data = pathinfo($file);
        $data['relative_path'] = str_replace($dir_root, '', $dir);
        $explode = explode('.', $data['filename']);
        $data['import_object'] = array_shift($explode);
        if (!empty($explode)) {
            $tmp = array_shift($explode);
        }
        if (strpos($tmp, 'preset_') !== false) {
            $data['preset_id'] = str_replace('preset_', '', $tmp);
        }
        $data['dirname'] = $dir;
        $data['priority'] = $priority[$data['import_object']];
        $files[] = $data;
    }

    $fs_files = fn_get_dir_contents($dir, false, true, 'xml');

    $priority = array('import' => 10, 'offers' => 20, 'orders' => 30);
    foreach ($fs_files as $file) {
        $data = pathinfo($file);
        list($data['import_object'], $tmp) = explode('.', $data['filename']);
        $data['dirname'] = $dir;
        $data['priority'] = $priority[$data['import_object']];
        $data['extension'] = 'xml';
        $files[] = $data;
    }
    return $files;
}

function fn_auto_exim_run_import($imports, $company_id) {
    Registry::set('runtime.company_id', $company_id);
    foreach ($imports as $import) {
        $res = false;
        // make sure no another import processes are still active
        $debug = isset($_REQUEST['debug']);

        $lock_file_path = $import['dirname'].$import['basename'] . '.lock';
        if (is_file($lock_file_path) && filemtime($lock_file_path) < (TIME - 60 * 60)) {
            @unlink($lock_file_path);
        }

        if (is_file($lock_file_path) && !$debug) {
            fn_echo($import['dirname'].$import['basename'] . " in process");
            continue;
        } else {
            $h = fopen($lock_file_path, 'w');
            if (!$h) {
                fn_echo("Cannot lock import file");
                continue;
            }
            fclose($h);
        }

        $start_time = fn_microtime_float();
        fn_log_event('exim', 'start_import', ['file' => $import['relative_path'].$import['basename']]);

        if (isset($import['preset_id']) && !empty($import['preset_id'])) {
            $cond = fn_get_company_condition('company_id', true, '', false, true);
            $preset_id = db_get_field("SELECT preset_id FROM ?:import_presets WHERE preset_id = ?i $cond", $import['preset_id']);
            if (empty($preset_id)) {
                fn_echo("Import not found");
            } else {
                // файла то там нет в пресете!!
                $presets_path = fn_get_files_dir_path();
                $res = fn_run_import($preset_id, str_replace($presets_path, '', $import['dirname'].$import['basename']));
            }
        } elseif ($import['extension'] == 'csv') {
            if (!empty($import['import_object'])) {
                $pattern = fn_exim_get_pattern_definition(strtolower($import['import_object']), 'import');
                
                if (!empty($pattern)) {
                    $default_params = array(
                        'delimiter' => ';',
                        'images_path' => 'images/',
                        'price_dec_sign_delimiter' => ',',
                        'category_delimiter' => '///',
                        'skip_creating_new_products' => 'N',
                        'unset_file' => true
                    );
                    if (is_array($import)) {
                        $params = array_merge($default_params, $import);
                    } else {
                        $params = $default_params;
                    }
                    if ($params['delimiter'] == ',') {
                        $params['delimiter'] = 'C';
                    }

                    Registry::set('runtime.skip_area_checking', true);

                    if (($data = fn_exim_get_csv($pattern, $params['dirname'].$params['basename'], $params))) {
                        foreach ($data as &$row) {
                            $row = array_filter($row, function($v) {return $v != '';});
                        }

                        $res = fn_import($pattern, $data, $params);
                    }
                } else {
                    fn_echo("Unexpected import type");
                }
            }
        } elseif ($import['extension'] == 'xml') {
            $path_commerceml = $import['dirname'];
            $log = new Logs('', $path_commerceml);
            Registry::set('runtime.company_id', $company_id);

            $exim_commerceml = new SDRusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);
            $manual = true;
            //unset($_SESSION['exim_1c']);
            $lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

            $exim_commerceml->import_params['lang_code'] = $lang_code;
            $exim_commerceml->import_params['manual'] = true;
            list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
            $exim_commerceml->company_id = $company_id;

            if ($import['import_object'] == 'import') {
                $filename = $import['basename'];
                list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);

                $exim_commerceml->addMessageLog($text_message);

                if ($d_status === false) {
                    fn_echo("failure");
                    exit;
                }

                if ($s_commerceml['exim_1c_import_products'] != 'not_import') {
                    $exim_commerceml->importDataProductFile($xml);
                } else {
                    fn_echo("success\n");
                }
                $res = true;
            }
            if ($action == 'offers') {
                $filename = $import['basename'];
                list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
                $exim_commerceml->addMessageLog($text_message);
                if ($d_status === false) {
                    fn_echo("failure");
                    exit;
                }
                if ($s_commerceml['exim_1c_only_import_offers'] == 'Y') {
                    $exim_commerceml->importDataOffersFile($xml, $service_exchange, $lang_code, $manual);
                } else {
                    fn_echo("success\n");
                }
                $res = true;
            }
            if ($import['import_object'] == 'orders') {
                $filename = $import['basename'];
                list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
                $exim_commerceml->addMessageLog($text_message);
                if ($d_status === false) {
                    fn_echo("failure");
                    exit;
                }

                $exim_commerceml->importFileOrders($xml, $lang_code);
                $res = true;
            }
        }

        $execution_time = fn_microtime_float() - $start_time;
        fn_log_event('exim', 'finish_import', ['file' => $import['relative_path'].$import['basename'], 'time' => $execution_time]);

        if ($res) {
            $dir = str_replace('autoload/', 'storage/', $import['dirname']);
            $file = fn_date_format(time(), "%H-%M-%d-%m-%Y.").$import['basename'];
            fn_mkdir($dir);
            fn_copy($import['dirname'].$import['basename'], $dir.$file);
            fn_rm($import['dirname'].$import['basename']);
        }
        @unlink($lock_file_path);
    }
}

if (!is_callable('fn_microtime_float')) {
function fn_microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
}

function fn_run_import($preset_id, $file = '') {
    /** @var \Tygh\Addons\AdvancedImport\Presets\Manager $presets_manager */
    $presets_manager = Tygh::$app['addons.advanced_import.presets.manager'];
    /** @var \Tygh\Addons\AdvancedImport\Presets\Importer $presets_importer */
    $presets_importer = Tygh::$app['addons.advanced_import.presets.importer'];


    list($presets,) = $presets_manager->find(false, array('ip.preset_id' => $preset_id));
    if ($presets) {

        Registry::set('runtime.advanced_import.in_progress', true, true);

        $preset = reset($presets);
        if (!empty($file)) {
            $preset['file'] = $file;
        }
        /** @var \Tygh\Addons\AdvancedImport\Readers\Factory $reader_factory */
        $reader_factory = ServiceProvider::getReadersFactory();

        $is_success = false;
        try {
            $reader = $reader_factory->get($preset);

            $fields_mapping = $_REQUEST['fields'] ?: $presets_manager->getFieldsMapping($preset['preset_id']);

            $pattern = $presets_manager->getPattern($preset['object_type']);
            $schema = $reader->getSchema();
            $schema->showNotifications();
            $schema = $schema->getData();

            $remapping_schema = $presets_importer->getEximSchema(
                $schema,
                $fields_mapping,
                $pattern
            );

            if ($remapping_schema) {
                $presets_importer->setPattern($pattern);
                $result = $reader->getContents(null, $schema);
                $result->showNotifications();

                $import_items = $presets_importer->prepareImportItems(
                    $result->getData(),
                    $fields_mapping,
                    $preset['object_type'],
                    true,
                    $remapping_schema
                );

                $presets_manager->update($preset['preset_id'], array(
                    'last_launch' => TIME,
                    'last_status' => ImportStatuses::IN_PROGRESS,
                ));

                $preset['options']['preset'] = $preset;
                unset($preset['options']['preset']['options']);

                return fn_import($pattern, $import_items, $preset['options']);
            }
        } catch (ReaderNotFoundException $e) {
            fn_echo(__('error_exim_cant_read_file'));
        } catch (PermissionsException $e) {
            fn_echo(__('advanced_import.cant_load_file_for_company'));
        } catch (FileNotFoundException $e) {
            fn_echo(__('advanced_import.cant_load_file_for_company'));
        }
    }
}
