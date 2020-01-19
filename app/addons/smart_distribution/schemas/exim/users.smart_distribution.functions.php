<?php

function fn_exim_rejoin_user_profiles_export(&$pattern)
{
  $pattern['references']['user_profiles'] = array(
    'reference_fields' => [
      'user_id' => '#key',
      'profile_id' => '$profile_id'
    ],
    'join_type' => 'LEFT'
  );
}

function fn_exim_smart_distribution_add_field_columns_import($import_data, &$pattern)
{
  //  get field names from columns
  $file_fields = array_keys(reset($import_data));

  $default_fields = [];
  foreach($pattern['export_fields'] as $name => $field) {
    $default_fields[] = $name;  //  default name
    if (isset($field['db_field'])) {
      $default_fields[] = $field['db_field'];   //  key name
    }
  }

  $field_names = array_diff($file_fields, $default_fields);

  if (empty($field_names)) {
    return;
  }

  $exist_fields = db_get_hash_array('SELECT object_id, description FROM ?:profile_field_descriptions WHERE description IN (?a) AND lang_code = ?s', 'description', $field_names, DESCR_SL);

  foreach ($exist_fields as $field) {
    $pattern['export_fields'][$field['description']] = array(
      'process_put' => array('fn_exim_smart_distribution_add_field_column_import', '#key', '#this', $field['description'], '%profile_id%'),
      'linked' => false
    );
  }
}

function fn_exim_smart_distribution_add_field_column_import($user_id, $value, $name, $profile_id)
{
  $profile_ids = fn_exim_smart_distribution_get_user_fields($user_id);

  //  set profile name for new users
  if (count($profile_ids) == 1 && !is_numeric($profile_id)) {

    $profile_name = db_get_field("SELECT profile_name
      FROM ?:user_profiles
      WHERE user_id = ?i AND profile_id",
      $user_id,
      $profile_ids[0]
    );

    if (!$profile_name) {
      db_query("UPDATE ?:user_profiles SET ?u WHERE user_id = ?i AND profile_id",
      ['profile_name' => $profile_id],
      $user_id,
      $profile_ids[0]);
    }

    $profile_id = $profile_ids[0];
  }

  $exist_fields = db_get_array('SELECT object_id, description FROM ?:profile_field_descriptions WHERE description IN (?a) AND lang_code = ?s', $name, DESCR_SL);

  foreach ($exist_fields as $f) {

    $field = fn_exim_smart_distribution_get_field_info($f['object_id']);

    if (empty($field) || empty($profile_ids)) {
      return;
    }

    //  update default fields main info
    if ($field['is_default'] == 'Y' && $field['section'] == 'C') {
      db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", [$field['field_name'] => $value], $user_id);
      continue;
    }

    $profile_fnc = function($profile_id) use ($field, $value, $user_id) {
      //  update default fields additional info
      if ($field['is_default'] == 'Y') {
          db_query("UPDATE ?:user_profiles SET ?u WHERE profile_id = ?i AND user_id = ?i", [$field['field_name'] => $value], $profile_id, $user_id);
      } else {

        $is_exist = db_get_field('SELECT field_id FROM ?:profile_fields_data WHERE object_id = ?i AND field_id = ?i AND object_type = ?s', $profile_id, $field['field_id'], 'P');

        if ($is_exist) {
          db_query("UPDATE ?:profile_fields_data SET ?u WHERE object_id = ?i AND field_id = ?i", ['value' => $value], $profile_id, $field['field_id']);
        } else {
          db_query("INSERT INTO ?:profile_fields_data ?e", [
            'object_id' => $profile_id,
            'field_id' => $field['field_id'],
            'object_type' => 'P',
            'value' => $value,
          ]);
        }
      }
    };

    //  if non exist $profile_id add for all profiles
    if (!$profile_id) {
      foreach ($profile_ids as $profile_id) {
        $profile_fnc($profile_id);
      }
    } else {
      $profile_fnc($profile_id);
    }
  }
}

function fn_exim_smart_distribution_get_field_info($field_id)
{
  static $fields = [];

  $fields[$field_id] = isset($fields[$field_id])
    ? $fields[$field_id]
    : db_get_row('SELECT field_id, field_name, is_default, section FROM ?:profile_fields WHERE field_id = ?i', $field_id);

  return $fields[$field_id];
}

function fn_exim_smart_distribution_get_user_fields($user_id)
{
  static $users = [];

  $users[$user_id] = isset($users[$user_id])
    ? $users[$user_id]
    : db_get_fields('SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i', $user_id);

  return $users[$user_id];
}

//  transfer vendor customer emails to ?:vendors_customers
function fn_exim_smart_distribution_add_vendors_customers($user_id, $vendor_emails)
{
  //  ПОСМОТРИ МЕНЯ
  //  Перенести это условие в самый вверх, если нужно пропускать пустые значения
  if (empty($vendor_emails)) {
    return true;
  }
  //  remove old data
  db_query("DELETE FROM ?:vendors_customers WHERE customer_id = ?i", $user_id);

  //  get vendor customer ids
  $vendor_customer_ids = db_get_fields(
    "SELECT user_id FROM ?:users WHERE email IN (?a)",
    array_map('trim', explode(',', $vendor_emails))
  );

  if ($vendor_customer_ids) {
    $insert_data = array_map(
      function($id) use ($user_id) {
        return [
          'vendor_manager' => $id,
          'customer_id' => $user_id
        ];
      },
      $vendor_customer_ids
    );

    db_query('INSERT INTO ?:vendors_customers ?m', $insert_data);
  }
}

function fn_exim_smart_distribution_export_vendors_customers($user_id) {
  $managers  = db_get_fields("SELECT email FROM ?:users LEFT JOIN ?:vendors_customers ON ?:users.user_id = ?:vendors_customers.vendor_manager WHERE customer_id = ?i", $user_id);
  return implode(',', $managers);
}

//  create profile, if empty profile_id
function fn_exim_smart_distribution_check_profile_id($id, &$object)
{
    if (isset($id['user_id'])
      && !empty($id['user_id'])
    ) {
      $profile_id = $object['profile_id'];

      //  check profile by profile id and name
      if ($profile_id) {
        $add = ($profile_id == __("main")) ? db_quote('OR profile_name = ?s', '') : '';
        $profile_id = db_get_field("SELECT profile_id
          FROM ?:user_profiles
          WHERE user_id = ?i AND (profile_id = ?i OR profile_name = ?s $add)",
          $id['user_id'],
          $object['profile_id'],
          $object['profile_id']
        );
      }

      //  create if !exist
      if (empty($profile_id)) {
        $profile_id = db_query("INSERT INTO ?:user_profiles ?e", [
          'user_id' => $id['user_id'],
          'profile_type' => 'S',
          'profile_name' => $object['profile_id']
            ? (String) $object['profile_id']
            : 'Import create'
        ]);
      }

      $object['profile_id'] = $profile_id;
    }
}
