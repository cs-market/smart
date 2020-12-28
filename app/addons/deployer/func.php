<?php

function fn_write_deploy_log($data, $filename = 'var/files/deploy.log')
    {
    $file = fopen($filename, 'a');

    if (!empty($file)) {
        fputs($file, 'TIME: ' . date('Y-m-d H:i:s', TIME) . "\n");
        fputs($file, fn_array2code_string($data) . "\n");
        fclose($file);
    }
}
