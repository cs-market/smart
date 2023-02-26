<?php

$schema['controllers']['tickets'] = array(
    'permissions' => true,
);
$schema['controllers']['mailboxes'] = array(
    'permissions' => true,
);
$schema['controllers']['tools']['modes']['update_status']['param_permissions']['table']['helpdesk_messages'] = true;

return $schema;
