<?php

$schema['/exim_1c'] = array (
    'dispatch' => 'ex_exim_1c'
);

$schema['/exim_cml'] = array (
    'dispatch' => 'ex_exim_1c',
    'service_exchange' => 'exim_cml'
);

$schema['/exim_moysklad'] = array (
    'dispatch' => 'ex_exim_1c',
    'service_exchange' => 'exim_moysklad'
);

$schema['/exim_class'] = array (
    'dispatch' => 'ex_exim_1c',
    'service_exchange' => 'exim_class'
);

return $schema;
