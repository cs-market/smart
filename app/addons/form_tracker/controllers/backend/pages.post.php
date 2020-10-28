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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'update') {
    $form = Tygh::$app['view']->getTemplateVars('form');
    $elements = Tygh::$app['view']->getTemplateVars('elements');
    foreach ($elements as $key => $elm) {
        if ($elm['element_type'] == FORM_IS_TRACKED) {
            $form['general'][FORM_IS_TRACKED] = $elm['value'];
            unset($elements[$key]);
        }
    }
    Tygh::$app['view']->assign('form', $form);
    Tygh::$app['view']->assign('elements', $elements);
}
