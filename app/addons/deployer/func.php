<?php

use Tygh\Registry;
use Tygh\Settings;
use Tygh\UpgradeCenter\Migrations\Migration;
use Tygh\Tools\SecurityHelper;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

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
            if ($addon['reset']) {
                exec('git reset --hard HEAD', $output);
                fn_write_deploy_log(reset($output));
            }
            if (!empty(trim($addon['migrations_path']))) {
                $old_migrations = fn_get_dir_contents($addon['migrations_path'], false, true, 'php');
            }
            exec('git pull ' . $addon['remote'] . ' ' . $addon['branch'], $output);
            fn_write_deploy_log('result: ' . reset($output));

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

            fn_clear_cache();
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
}

function fn_deployer_webhook_info() {
    $token = Registry::get('addons.deployer.token');
    return __('deployer.webhook_info', ['[url]' => fn_url("deployer.run_deploy&token=$token", 'C')]);
}
