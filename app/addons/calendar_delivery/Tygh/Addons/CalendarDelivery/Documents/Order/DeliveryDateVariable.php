<?php

namespace Tygh\Addons\CalendarDelivery\Documents\Order;

use Tygh\Template\Document\Order\Context;
use Tygh\Template\IActiveVariable;
use Tygh\Template\IVariable;
use Tygh\Tools\Formatter;
use Tygh\Registry;

/**
 * Class RewardPointVariable
 * @package Tygh\Addons\RewarPoints\Documents\Order
 */
class DeliveryDateVariable implements IVariable, IActiveVariable
{
    public $date;
    public $raw = array();

    public function __construct(Context $context, Formatter $formatter)
    {
        $order = $context->getOrder();

        if (!empty($order->data['delivery_date'])) {
            $this->delivery_date = $formatter->asDatetime($order->data['delivery_date'], Registry::get('settings.Appearance.date_format'));
            $this->raw['delivery_date'] = $order->data['delivery_date'];
        }
    }

    /**
     * @inheritDoc
     */
    public static function attributes()
    {
        return array(
            'delivery_date',
            'raw' => array(
                'delivery_date'
            )
        );
    }
}
