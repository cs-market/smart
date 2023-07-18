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
use Tygh\Settings;
use Tygh\UpgradeCenter\Migrations\Migration;
use Tygh\Tools\SecurityHelper;
use Tygh\Validators;

defined('BOOTSTRAP') or die('Access denied');

function fn_write_deploy_log($data, $filename = 'var/files/deploy.log') {
    $file = fopen($filename, 'a');

    if (!empty($file)) {
        fputs($file, 'TIME: ' . date('Y-m-d H:i:s', TIME) . "\n");
        fputs($file, fn_array2code_string($data) . "\n");
        fclose($file);
    }
}

function fn_deploy($webhook) {
    if ( isset( $webhook['push'] ) ) {
        $lastChange = $webhook['push']['changes'][ count( $webhook['push']['changes'] ) - 1 ]['new'];
        $branch = isset( $lastChange['name'] ) && ! empty( $lastChange['name'] ) ? $lastChange['name'] : '';
        $addon = Registry::get('addons.deployer');
        if ($branch = $addon['branch']) {
            $current_dir = getcwd();
            if ($addon['reset']) {
                chdir($addon['git_path']);
                exec('git reset --hard HEAD', $output);
                chdir($current_dir);
                fn_write_deploy_log(reset($output));
            }
            if (!empty(trim($addon['migrations_path']))) {
                $old_migrations = fn_get_dir_contents($addon['migrations_path'], false, true, 'php');
                $old_sql_files = fn_get_dir_contents($addon['migrations_path'], false, true, array('.sql', '.tgz', '.zip'));
            }

            chdir($addon['git_path']);
            exec('git pull ' . $addon['remote'] . ' ' . $addon['branch'], $output);
            chdir($current_dir);
            fn_write_deploy_log('result: ' . reset($output));

            // apply phinx migrations
            if (!empty(trim($addon['migrations_path']))) {
                $current_migrations = fn_get_dir_contents($addon['migrations_path'], false, true, 'php');
                $new_migrations = array_diff($current_migrations, $old_migrations);
                if (!empty($new_migrations)) {
                    fn_mkdir($addon['migrations_path'] . 'run/');
                    foreach ($new_migrations as $migration_file) {
                        $failed_copy[$migration_file] = !fn_copy($addon['migrations_path'].$migration_file, $addon['migrations_path'] . 'run/' . $migration_file);
                    }
                    $failed_copy = array_filter($failed_copy);
                    if (!empty($failed_copy)) {
                        fn_write_deploy_log('failed to copy: ' . implode(', ',$failed_copy));
                    } else {
                        fn_write_deploy_log('run migrations');
                        $config = array(
                            'migration_dir' => $addon['migrations_path'] . 'run/'
                        );

                        $migration_succeed = Migration::instance($config)->migrate(0);

                        if ($migration_succeed) {
                            fn_write_deploy_log('migrations finished');
                        } else {
                            fn_write_deploy_log('failed to run migrations');
                        }
                    fn_rm($addon['migrations_path'] . 'run/', true);
                    }
                }
            }

            //apply zip and sql backups
            fn_mkdir($addon['migrations_path'] . 'run/');
            $sql_files = fn_get_dir_contents($addon['migrations_path'], false, true, array('.sql', '.tgz', '.zip'));
            $new_sql_files = array_diff($sql_files, $old_sql_files);
            foreach ($new_sql_files as $file) {
                $ext = fn_get_file_ext($addon['migrations_path'] . $file);

                if ($ext == 'tgz' && !$validators->isPharDataAvailable()) {
                    continue;
                }
                if ($ext == 'zip' && !$validators->isZipArchiveAvailable()) {
                    continue;
                }
                $restore_result = DataKeeper::restore($file);
                if ($restore_result === true) {
                    fn_write_deploy_log(__('done') . ': ' . $file);
                } else {
                    fn_write_deploy_log(__('error_occured') . ': ' . $file);
                }
            }

            //fn_clear_cache();
            fn_clear_template_cache();
        }
    }
}

function fn_deployer_install() {
    Settings::instance()->updateValue(
        'token',
        SecurityHelper::generateRandomString(),
        'deployer'
    );

    Settings::instance()->updateValue(
        'git_path',
        DIR_ROOT,
        'deployer'
    );
}

function fn_deployer_webhook_info() {
    $token = Registry::get('addons.deployer.token');
    return __('deployer.webhook_info', ['[url]' => fn_url("deployer.run_deploy&token=$token", 'C')]);
}
