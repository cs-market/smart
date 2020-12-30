<?php

$schema['delivery_date'] = array(
    'class' => '\Tygh\Addons\CalendarDelivery\Documents\Order\DeliveryDateVariable',
    'arguments' => array('#context', '@formatter'),
);

return $schema;