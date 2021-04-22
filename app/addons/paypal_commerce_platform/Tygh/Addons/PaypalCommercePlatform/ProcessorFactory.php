<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Addons\PaypalCommercePlatform;

use Tygh\Addons\PaypalCommercePlatform\Api\ClientWrapper;
use Tygh\Addons\PaypalCommercePlatform\Payments\PaypalCommercePlatform;
use Tygh\Database\Connection;
use Tygh\Tools\Formatter;

class ProcessorFactory
{
    /** @var \Tygh\Database\Connection $db */
    protected $db;

    /** @var \Tygh\Tools\Formatter $formatter */
    protected $formatter;

    /** @var \Tygh\Addons\PaypalCommercePlatform\OAuthHelper $oauth_helper */
    protected $oauth_helper;

    /** @var array<string, string> $status_conversion_schema */
    protected $status_conversion_schema;

    /**
     * ProcessorFactory constructor.
     *
     * @param \Tygh\Database\Connection                       $db                       Database connection
     * @param \Tygh\Tools\Formatter                           $formatter                Price formatter
     * @param \Tygh\Addons\PaypalCommercePlatform\OAuthHelper $oauth_helper             OAuth helper
     * @param array<string, string>                           $status_conversion_schema Order status conversion schema
     */
    public function __construct(
        Connection $db,
        Formatter $formatter,
        OAuthHelper $oauth_helper,
        array $status_conversion_schema
    ) {
        $this->db = $db;
        $this->formatter = $formatter;
        $this->oauth_helper = $oauth_helper;
        $this->status_conversion_schema = $status_conversion_schema;
    }

    /**
     * Constructs payment method processor with default components by the payment method ID.
     *
     * @param int                        $payment_id       Payment method ID
     * @param array<string, string>|null $processor_params Payment method configuration
     *
     * @psalm-param array{
     *   access_token: string,
     *   client_id: string,
     *   expiry_time: int,
     *   mode: string,
     *   secret: string,
     *   payer_id: string,
     *   currency: string
     * } $processor_params
     *
     * @return \Tygh\Addons\PaypalCommercePlatform\Payments\PaypalCommercePlatform
     */
    public function getByPaymentId($payment_id, array $processor_params = null)
    {
        if ($processor_params === null) {
            $processor_params = PaypalCommercePlatform::getProcessorParameters($payment_id);
        }

        return new PaypalCommercePlatform(
            $payment_id,
            $processor_params,
            $this->status_conversion_schema,
            $this->db,
            $this->formatter,
            new ClientWrapper($payment_id, $processor_params, $this->db),
            $this->oauth_helper
        );
    }
}
