<?php

use Tygh\Registry;
use Tygh\UpgradeCenter\Migrations\Migration;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $addon = Registry::get('addons.deployer');

    //$body = json_decode(file_get_contents('php://input'), true);
    if ($addon['reset']) {
        exec('git reset --hard HEAD', $output);
        fn_write_deploy_log(reset($output));
    }
    if (!empty(trim($addon['migrations_path']))) {
        $initial_migrations = fn_get_dir_contents($addon['migrations_path'], false, true, 'php');
    }
    exec('git pull ' . $addon['remote'] . ' ' . $addon['branch'], $output);
    fn_write_deploy_log('result: ' . reset($output));

    if (!empty(trim($addon['migrations_path']))) {
        $current_migrations = fn_get_dir_contents($addon['migrations_path'], false, true, 'php');
        $run_migrations = array_diff($current_migrations, $initial_migrations);
        if (!empty($run_migrations)) {
        fn_mkdir($addon['migrations_path'] . 'run/');
        foreach ($run_migrations as $migration_file) {
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

    die("OK");
}