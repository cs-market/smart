<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_establish_marr_ftp_connection() {
    $settings = Registry::get('addons.exim_csv_marr');
    if (function_exists('ftp_connect')) {
        if (!empty($settings['ftp_hostname'])) {
            $ftp_port = !empty($settings['ftp_port']) ? $settings['ftp_port'] : '21';
            if (substr_count($settings['ftp_hostname'], ':') > 0) {
                $start_pos = strrpos($settings['ftp_hostname'], ':');
                $ftp_port = substr($settings['ftp_hostname'], $start_pos + 1);
                $settings['ftp_hostname'] = substr($settings['ftp_hostname'], 0, $start_pos);
            }

            $ftp = @ftp_connect($settings['ftp_hostname'], $ftp_port);
            if (!empty($ftp)) {
                if (@ftp_login($ftp, $settings['ftp_username'], $settings['ftp_password'])) {
                    ftp_pasv($ftp, true);
                    return $ftp;
                }
            }
        }
    }
    return false;
}

function fn_exim_csv_marr_auto_exim_find_files($dir, $cid) {
    if ($cid == '1797') {
        $ftp = fn_establish_marr_ftp_connection();
        if ($ftp) {
            @ftp_chdir($ftp, Registry::get('addons.exim_csv_marr.input_directory'));
            $files = ftp_nlist($ftp, '.');
    
            if ($files) {
                foreach ($files as $file) {
                    if(fn_ftp_is_file($ftp, $file)) {
                        fn_mkdir($dir);
                        if (ftp_get($ftp, $dir.$file, $file, FTP_BINARY)) {
                            @ftp_delete($ftp, $file);
                            @ftp_chdir($ftp, 'Archive');
                            ftp_put($ftp, time() . '_' . $file, $dir.$file,  FTP_BINARY);
                        }
                    }
                }
            }
            @ftp_close($ftp);
        }
    }
}

function fn_exim_csv_marr_export_order_to_csv($pattern, $options, $res, $order) {
    if ($res && $order['company_id'] == '1797') {
        $ftp = fn_establish_marr_ftp_connection();
        if ($ftp) {
            @ftp_chdir($ftp, Registry::get('addons.exim_csv_marr.output_directory'));
            if (ftp_put($ftp, fn_basename(fn_get_files_dir_path().$options['filename']), fn_get_files_dir_path().$options['filename'],  FTP_BINARY)) {
                fn_rm(fn_get_files_dir_path().$options['filename']);
            }
            @ftp_close($ftp);
        }
    }
}

function fn_ftp_is_file($ftp, $file) {
    return (ftp_mdtm($ftp, $file) != '-1');
}