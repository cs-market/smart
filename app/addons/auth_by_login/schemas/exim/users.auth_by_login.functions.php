<?php

function fn_exim_auth_by_login_get_primary_object_id_sync_email_login(&$object, &$skip_get_primary_object_id)
{
  if ( fn_auth_by_login_check_skip_obj($object) ) {
    $skip_get_primary_object_id = true;
    return;
  }
}

function fn_exim_auth_by_login_sync_email_login(&$object, &$skip_record, &$processed_data)
{
  if ( fn_auth_by_login_check_skip_obj($object) ) {
    $processed_data['S']++;
    $skip_record = true;
    return;
  }

  //  sync info
  if (isset($object['email']) && isset($object['user_login'])) {
    if (!$object['email'] && $object['user_login']) {
      $object['email'] = $object['user_login'];
      $skip_get_primary_object_id = true;
    } elseif ($object['email'] && !$object['user_login']) {
      $object['user_login'] = $object['email'];
    }
  }
}

/*
 * return Bool:
 *  true  -- skip
 *  false -- identifique data exist
 */
function fn_auth_by_login_check_skip_obj($object)
{
  //  both emty || only one & empty || !exist => skip
  if (
    (!isset($object['email']) && !isset($object['user_login']))
    || (
      !isset($object['email']) && !$object['user_login']
    )
    || (
      !isset($object['user_login']) && !$object['email']
    )
    || (
      isset($object['email']) && !$object['email']
      && isset($object['user_login']) && !$object['user_login']
    )
  ) {
    return true;
  } else {
    return false;
  }
}
