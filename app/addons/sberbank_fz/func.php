<?php

defined('BOOTSTRAP') or die('Access denied');

function fn_sberbank_fz_install()
{
    fn_sberbank_fz_uninstall();

    $_data = array(
        'processor' => 'Сбербанк Онлайн с ФЗ-54',
        'processor_script' => 'sberbank_fz.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'sberbank_fz.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'addon' => 'sberbank_fz'
    );

    db_query("INSERT INTO ?:payment_processors ?e", $_data);
}

function fn_sberbank_fz_uninstall()
{
    db_query("DELETE FROM ?:payment_processors WHERE processor_script = ?s", "sberbank_fz.php");
}

function fn_sberbank_fz_normalize_phone($phone)
{
    $phone_normalize = '';

    if (!empty($phone)) {
        if (strpos('+', $phone) === false && $phone[0] == '8') {
            $phone[0] = '7';
        }

        $phone_normalize = str_replace(array(' ', '(', ')', '-'), '', $phone);
    }

    return $phone_normalize;
}

/**
 * The "get_payment_processors_post" hook handler.
 *
 * Actions performed:
 *     - Adds specific 'russian' attribute to some payment processors for categorization.
 *
 * @see \fn_get_payment_processors()
 */
function fn_sberbank_fz_get_payment_processors_post($lang_code, &$processors)
{
    foreach ($processors as &$processor) {
        // if ($processor['addon'] === 'sberbank_fz') {
        //     $processor['russian'] = true;
        // }
    }
    unset($processor);
}
