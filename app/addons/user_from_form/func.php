<?php

// use Tygh\Registry;

//  [HOOKs]
function fn_user_from_form_send_form($page_data, $form_values, $result, $from, $sender, $attachments, $is_html, $subject)
{
  if ($result != true) {
    return;
  }

  $schema = fn_get_schema('user_from_form', 'schema');

  if (!in_array($page_data['page_id'], array_keys($schema))) {
    return;
  }

  $form_schema = $schema[$page_data['page_id']];

  $user_data = fn_create_user_field_from_form($form_schema['fields_map'], $form_values);

  $user_data = array_merge(
    $user_data,
    [
      'user_type' => 'C',
      'company_id' => $form_schema['company_id'],
    ]
  );

  list($user_id) = fn_update_user(
      '',
      $user_data,
      $auth,
      true,   // ship_to_another
      false   // notify_customer
  );

  if ($user_id && $form_schema['usergroup_id']) {
    foreach ($form_schema['usergroup_id'] as $id) {
      //  main usergroups
      if (in_array($id, ['0', '1'])) {
        continue;
      }

      fn_change_usergroup_status('A', $user_id, $id);
    }
  }
  if ($user_id) {
    $result = db_query("UPDATE ?:users SET status = ?s WHERE user_id = ?i", "A", $user_id);
  }
}
//  [HOOKs]

function fn_create_user_field_from_form($fields, $values)
{
  $field_data = [];

  foreach ($fields as $key => $name) {
    if (is_array($name)) {
      foreach ($name as $n) {
        $field_data[$n] = $values[$key];
      }
    } else {
      $field_data[$name] = $values[$key];
    }
  }

  return $field_data;
}
