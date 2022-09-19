<?php

use Tygh\Pdf;
use Tygh\Registry;

//  [HOOKs]
function fn_user_from_form_send_form(&$page_data, &$form_values, &$result, $from, $sender, $attachments, $is_html, $subject)
{
    if ($result != true) {
        return;
    }

    $schema = fn_get_schema('user_from_form', 'schema');

    if (!in_array($page_data['page_id'], array_keys($schema))) {
        return;
    }

    $form_schema = $schema[$page_data['page_id']];
    if (!$_SESSION['auth']['user_id']) {
        $user_data = fn_create_user_field_from_form($form_schema['fields_map'], $form_values);

        $user_data = array_merge(
            $user_data,
            [
                'user_type' => 'C',
                'company_id' => $form_schema['company_id'],
            ]
        );

        if (isset($form_schema['managers'])) {
            $user_data['managers'] = $form_schema['managers'];
        }
        
        Registry::set('settings.General.approve_user_profiles', 'N');
        list($user_id) = fn_update_user(
                '',
                $user_data,
                $auth,
                true,   // ship_to_another
                false   // notify_customer
        );
    } else {
        $user_id = $_SESSION['auth']['user_id'];
    }

    if ($form_schema['render_document']) {
        fn_send_document_from_form($form_schema['document_map'], $form_values, $form_schema['render_document'], $user_id);
    }

    if ($user_id && $form_schema['usergroup_id']) {
        foreach ($form_schema['usergroup_id'] as $id) {
            //  main usergroups
            if (in_array($id, ['0', '1'])) {
                continue;
            }

            fn_change_usergroup_status('A', $user_id, $id);
        }
    }

    if ($form_schema['add_user_data_to_email']) {
        $page_data['form']['elements'] = [
            'user_name' => [
                'element_id' => 'user_name', 
                'page_id' => $page_data['page_id'], 
                'parent_id' => 0, 
                'element_type' => 'T', 
                'value' => '', 
                'position' => 1, 
                'required' => 'Y', 
                'status' => 'A', 
                'description' => __('first_name_and_last_name')
            ],
            'user_code' => [
                'element_id' => 'user_code', 
                'page_id' => $page_data['page_id'], 
                'parent_id' => 0, 
                'element_type' => 'T', 
                'value' => '', 
                'position' => 1, 
                'required' => 'Y', 
                'status' => 'A', 
                'description' => __('code')
            ],
            'vendor' => [
                'element_id' => 'vendor', 
                'page_id' => $page_data['page_id'], 
                'parent_id' => 0, 
                'element_type' => 'T', 
                'value' => '', 
                'position' => 1, 
                'required' => 'Y', 
                'status' => 'A', 
                'description' => __('company_name')
            ],
        ] + $page_data['form']['elements'];

        $user = fn_get_user_info($user_id);
        $form_values['user_name'] = $user['firstname'];
        $form_values['user_code'] = $user['fields']['38'];
        $form_values['vendor'] = fn_get_company_name($user['company_id']);
    }

    if ($user_id && !$user_data['status']) {
        db_query("UPDATE ?:users SET status = ?s WHERE user_id = ?i", "A", $user_id);
    }

    if (!$user_id) $result = false;
}
//  [HOOKs]

function fn_create_user_field_from_form($fields, $values)
{
    $field_data = [];

    if (isset($fields['password'])) {
        $fields['password1'] = $fields['password'];
        $fields['password2'] = $fields['password'];
    }

    $fn_check_form_const = function($key) use ($values) {
        return $values[$key] ?? $key;
    };

    foreach ($fields as $name => $key) {
        if (is_array($key)) {
            $str = [];
            foreach ($key as $n) {
                $str[] = $fn_check_form_const($n);
            }

            $data = implode(' ', $str);
        } else {
            $data = $fn_check_form_const($key);
        }

        $field_data = fn_array_merge(
            $field_data,
            fn_profile_field_data_array($name, $data)
        );
    }

    return $field_data;
}

//  return array with key
//    name if default
//    [fields][field_id]
//    [] if not fount field
function fn_profile_field_data_array($name, $data)
{
    static $fields_info;

    if (!$fields_info) {
        $fields_info = db_get_hash_array("SELECT field_id, field_name, is_default FROM ?:profile_fields", 'field_name');
    }

    $return = [];
    $id = null;

    if (isset($fields_info[$name])) {
        if ($fields_info[$name]['is_default'] == 'Y') {
            $return[$name] = $data;
        } else {
            $return['fields'][$fields_info[$name]['field_id']] = $data;
        }
    } elseif (strpos($name, 'field_') !== false) {
        $id = (Int) str_replace('field_', '', $name);
        $return['fields'][$id] = $data;
    } else {
        $return[$name] = $data;
    }

    return $return;
}

function fn_send_document_from_form($map, $values, $page_id, $user_id) {
    $page = fn_get_page_data($page_id);
    $document = $page['description'];
    if (!empty($document)) {
        preg_match_all('((?<=\[).*?(?=\]))', $document, $matches);
        $map = array_flip($map);
        $replace = array();
        foreach ($matches[0] as $key => $value) {
            $replace['[' . $value . ']'] = (!empty($values[$map[$value]])) ? $values[$map[$value]] : '';
        }

        $document = str_replace(array_keys($replace), array_values($replace), $document);
        fn_mkdir(fn_get_files_dir_path());
        $filename = 'contract' . '-' . $user_id . '.pdf';
        $filepath = fn_get_files_dir_path() . $filename;

        if (Pdf::render($document, $filepath, true)) {
            $attachments[$filename] = $filepath;
        }
        $mailer = Tygh::$app['mailer'];
        $user_data = fn_get_user_info($user_id);

        $result = $mailer->send(array(
                'to' => $user_data['email'],
                'from' => 'company_orders_department',
                'body' => $page['page'],
                'subj' => $page['page'],
                'company_id' => $order_info['company_id'],
                'attachments' => $attachments
        ), 'A', CART_LANGUAGE);

        foreach ($attachments as $name => $path) {
                fn_rm($path);
        }
    }
}
