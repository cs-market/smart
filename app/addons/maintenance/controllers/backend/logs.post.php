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

defined('BOOTSTRAP') or die('Access denied');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return [CONTROLLER_STATUS_OK];
}

if ($mode == 'manage') {
    $view = Tygh::$app['view'];

    if ($logs = $view->getTemplateVars('logs')) { // Repay is allowed
        foreach($logs as &$log) {
            if ($log['type'] == 'general' && $log['action'] == 'debug') {
                foreach($log['content'] as &$data) {
                    $v = unserialize($data);
                    if (is_array($v)) {
                        $output = '<div><ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';
                        $v = htmlspecialchars(print_r($v, true));
                        if ($v == '') { $v = ' '; }
                        $output .= '<li><pre>' . $v . "\n" . '</pre></li></ol></div><div style="clear:left;"></div>';
                        $data = $output;
                    }
                }
            }
        }

        $view->assign('logs', $logs);
    }
}
