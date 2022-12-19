<?php

use Tygh\Pdf;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//  [HOOKs]
function fn_invoice_for_payment_update_company_pre(&$company_data, $company_id, $lang_code, $can_update)
{
    if (isset($company_data['invoice_for_payment'])) {
        $company_data['invoice_for_payment'] = serialize($company_data['invoice_for_payment']);
    }
}

function fn_invoice_for_payment_get_company_data_post($company_id, $lang_code, $extra, &$company_data)
{
    if (isset($company_data['invoice_for_payment'])) {
        $company_data['invoice_for_payment'] = unserialize($company_data['invoice_for_payment']);
    }
}
//  [/HOOKs]

function fn_install_invoice_payment() {
    $processor_data = array(
        'processor' => 'Выставить счет от вендора',
        'processor_script' => 'invoice_payment.php',
        'processor_template' => 'addons/invoice_for_payment/views/orders/components/payments/invoice_payment.tpl',
        'admin_template' => 'invoice_payment.tpl',
        'callback' => 'N',
        'type' => 'P',
        'position' => 90,
        'addon' => 'invoice_for_payment'
    );
    $processor_id = db_query('INSERT INTO ?:payment_processors ?e', $processor_data);
}

function fn_uninstall_invoice_payment() {
    db_query('DELETE FROM ?:payment_processors WHERE admin_template = ?s', 'invoice_payment.tpl');
}

/**
* Generate print invoice_for_payment and return it
*
* @param    mixed   $order_ids Order IDs (integer or array)
* @param    array   $params     Params. Available parans: area, lang_code, pdf, html_wrap
* @return string                        Result HTML or PDF
*/
function fn_print_order_invoices_for_payment($order_ids, $params = array())
{
    // Default params
    $params = array_merge(array(
        'pdf'            => false,
        'area'          => AREA,
        'lang_code' => CART_LANGUAGE,
        'secondary_currency' => CART_SECONDARY_CURRENCY,
        'html_wrap' => true,
        'save'          => false, // Save PDF
    ), $params);

    $html = array();
    $data = array();

    if (!is_array($order_ids)) {
        $order_ids = array($order_ids);
    }

    if ($params['pdf']) {
        fn_disable_live_editor_mode();
    }

    foreach ($order_ids as $order_id) {
        $order_info = fn_get_order_info($order_id, false, true, false, false, $params['lang_code']);

        if (empty($order_info)) {
            continue;
        }

        $data['order_info'] = $order_info;

        $data['company_data'] = fn_get_company_placement_info($order_info['company_id'], $params['lang_code']);
        $company = fn_get_company_data($order_info['company_id'], $params['lang_code']);
        $data['company_data']['invoice_for_payment'] = $company['invoice_for_payment'];

        /** @var \Tygh\SmartyEngine\Core $view */
        $view = Tygh::$app['view'];
        foreach ($data as $key => $value) {
            $view->assign($key, $value);
        }

        $template = 'addons/invoice_for_payment/print_invoice_payment.tpl';
        $html[] = $view->displayMail(
            $template, false, $params['area'], $order_info['company_id'], $params['lang_code']
        );

        if (!$params['pdf'] && $order_id != end($order_ids)) {
            $html[] = "<div style='page-break-before: always;'>&nbsp;</div>";
        }
    }

    if ($params['pdf']) {
        $filename = __('invoice_for_payment') . '-' . implode('-', $order_ids);
        if ($params['save']) {
            fn_mkdir(fn_get_files_dir_path());
            $filename = fn_get_files_dir_path() . $filename . '.pdf';
        }
        $result = Pdf::render($html, $filename, $params['save']);
        return $params['save'] ? $filename : $result;
    }

    return implode(PHP_EOL, $html);
}
