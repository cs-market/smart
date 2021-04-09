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

namespace Tygh\Addons\StripeConnect\Payments;

use Exception;
use Stripe\Account;
use Stripe\BalanceTransaction;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Transfer;
use Tygh\Addons\StripeConnect\PayoutsManager;
use Tygh\Addons\StripeConnect\PriceFormatter;
use Tygh\Common\OperationResult;
use Tygh\Database\Connection;
use Tygh\Enum\ObjectStatuses;
use Tygh\Addons\StripeConnect\Logger;
use Tygh\Addons\StripeConnect\StripeException;

/**
 * Class StripeConnect implements Stipe Connect payment method.
 * Uses Transfers: the whole payment is collected by the store owner and withdrawals are performed via Transfers.
 *
 * @package Tygh\Addons\StripeConnect\Payments
 */
class StripeConnect
{
    /** @var string */
    const API_VERSION = '2019-05-16';

    /** @var string */
    const PAYMENT_INTENT_STATUS_SUCCEDED = 'succeeded';

    /** @var string */
    const PAYMENT_INTENT_STATUS_REQUIRES_ACTION = 'requires_action';

    /** @var string */
    const PAYMENT_INTENT_STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';

    /** @var string $processor_script */
    protected static $processor_script = 'stripe_connect.php';

    /** @var string $payment_name */
    protected static $payment_name = 'stripe_connect';

    /** @var array $receivers_cache */
    protected static $receivers_cache = [];

    /** @var array $order_info */
    protected $order_info = [];

    /** @var array $processor_params */
    protected $processor_params = [];

    /** @var int $payment_id */
    protected $payment_id;

    /** @var Connection $db */
    protected $db;

    /** @var array $addon_settings */
    protected $addon_settings;

    /** @var PriceFormatter $price_formatter */
    protected $price_formatter;

    /** @var \Stripe\Charge[] */
    protected $charges_cache = [];

    /** @var int[] */
    protected $nets_cache = [];

    /**
     * StripeConnect constructor.
     *
     * @param int                                       $payment_id       Payment method ID
     * @param \Tygh\Database\Connection                 $db               Database connection
     * @param \Tygh\Addons\StripeConnect\PriceFormatter $price_formatter  Formatter
     * @param array                                     $addon_settings   Stripe Connect add-on settings
     * @param array|null                                $processor_params Payment processor configuration.
     *                                                                    When set to null, will be obtained from the
     *                                                                    database
     */
    public function __construct(
        $payment_id,
        Connection $db,
        PriceFormatter $price_formatter,
        array $addon_settings,
        $processor_params = null
    ) {
        $this->payment_id = $payment_id;
        $this->db = $db;
        $this->price_formatter = $price_formatter;
        $this->addon_settings = $addon_settings;

        if ($processor_params === null) {
            $this->processor_params = static::getProcessorParameters($payment_id);
        } else {
            $this->processor_params = $processor_params;
        }

        Stripe::setApiKey($this->processor_params['secret_key']);
        Stripe::setClientId($this->processor_params['client_id']);
        Stripe::setApiVersion(self::API_VERSION);
        Stripe::setLogger(new Logger());
    }

    /**
     * Obtains Stripe Connect based payment method processor parameters.
     *
     * @param int|null $payment_id If specified, processor parameters of the specified payment method will be returned.
     *                             Otherwise, first suitable method will be used.
     *
     * @return array
     */
    public static function getProcessorParameters($payment_id = null)
    {
        if ($payment_id === null) {
            $processor = fn_get_processor_data_by_name(static::getScriptName());
            if (isset($processor['processor_id'])) {
                $payments = fn_get_payment_by_processor($processor['processor_id']);
                foreach ($payments as $payment) {
                    if ($payment['status'] === ObjectStatuses::ACTIVE) {
                        $payment_id = $payment['payment_id'];
                        break;
                    }
                }
            }
        }

        if ($payment_id && $processor_data = fn_get_processor_data($payment_id)) {
            return $processor_data['processor_params'];
        }

        Logger::log(Logger::ACTION_FAILURE, __('stripe_connect.stripe_processor_params_missing'));

        return [
            'client_id'       => null,
            'publishable_key' => null,
            'secret_key'      => null,
            'currency'        => null,
        ];
    }

    /**
     * Gets payment processor script name.
     *
     * @return string
     */
    public static function getScriptName()
    {
        return static::$processor_script;
    }

    /**
     * Gets payment method name.
     *
     * @return string
     */
    public static function getPaymentName()
    {
        return static::$payment_name;
    }

    /**
     * Performs payment.
     *
     * @param array $order_info Order to pay for.
     *
     * @return array Payment processor response
     */
    public function chargeWith3DSecure(array $order_info)
    {
        $pp_response = [
            'order_status'                     => STATUS_INCOMPLETED_ORDER,
            'reason_text'                      => '',
            'stripe_connect.payment_intent_id' => '',
            'stripe_connect.token'             => '',
        ];

        $this->order_info = $order_info;

        $orders_queue = $this->getOrdersToCharge($order_info);

        // Check that all receivers are valid accounts
        if (!$this->validateOrdersQueueReceivers($orders_queue)) {
            $pp_response['order_status'] = 'F';

            return $pp_response;
        }

        try {
            $payment_intent = PaymentIntent::retrieve($order_info['payment_info']['stripe_connect.payment_intent_id']);
            if ($payment_intent->status === self::PAYMENT_INTENT_STATUS_REQUIRES_CONFIRMATION) {
                $payment_intent->confirm();
            }

            $charge = $this->getChargeByPaymentIntent($payment_intent);
            $charge->metadata['order_id'] = $order_info['order_id'];
            $charge->save();

            fn_update_order_payment_info(
                $order_info['order_id'],
                [
                    'stripe_connect.charge_id' => $charge->id,
                ]
            );

            foreach ($orders_queue as $order_id => $company_id) {
                if (!$company_id) {
                    // fallback for Vendor debt payout
                    continue;
                }

                $suborder_info = fn_get_order_info($order_id);
                $payouts_manager = new PayoutsManager($company_id);

                if (!empty($suborder_info['use_gift_certificates'])) {
                    fn_update_order_staff_only_notes($order_id, __('stripe_connect.gift_certificate_used', [
                        '[product]' => PRODUCT_NAME
                    ]));
                }

                if (!$this->formatAmount($suborder_info['total'])) {
                    continue;
                }

                if (!empty($suborder_info['use_gift_certificates'])) {
                    continue;
                }

                $transfer = $this->transferFunds($suborder_info, $payouts_manager, $charge);
                if (!$transfer) {
                    continue;
                }

                fn_update_order_payment_info(
                    $order_id,
                    [
                        'stripe_connect.transfer_id' => $transfer->id,
                        'stripe_connect.payment_id'  => $transfer->destination_payment,
                    ]
                );

                $withdrawal = $transfer->metadata['withdrawal'];
                if (!$withdrawal) {
                    continue;
                }

                $payouts_manager->createWithdrawal($withdrawal, $order_id);
                if (!$this->addon_settings['collect_payouts']) {
                    continue;
                }

                $payouts_manager->acceptPayouts();
            }

            $pp_response['order_status'] = 'P';
        } catch (Exception $e) {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = $e->getMessage();

            Logger::logException($e);
        }

        return $pp_response;
    }

    /**
     * Handles payment process when the 3-D Secure option is disabled.
     *
     * @param array<string, string|int|float> $order_info Order to charge
     *
     * @return array<string, string>
     */
    public function chargeWithout3DSecure(array $order_info)
    {
        $pp_response = [
            'order_status'                     => STATUS_INCOMPLETED_ORDER,
            'reason_text'                      => '',
            'stripe_connect.payment_intent_id' => '',
            'stripe_connect.token'             => '',
        ];

        $this->order_info = $order_info;

        $orders_queue = $this->getOrdersToCharge($order_info);

        // Check that all receivers are valid accounts
        if (!$this->validateOrdersQueueReceivers($orders_queue)) {
            $pp_response['order_status'] = 'F';

            return $pp_response;
        }

        $charges_to_capture = [];
        $customer = null;
        try {
            $customer = $this->createCustomer($order_info);

            foreach ($orders_queue as $order_id => $company_id) {
                $suborder_info = fn_get_order_info($order_id);
                $payouts_manager = new PayoutsManager($company_id);

                if (!empty($suborder_info['use_gift_certificates'])) {
                    fn_update_order_staff_only_notes($order_id, __('stripe_connect.gift_certificate_used', [
                        '[product]' => PRODUCT_NAME
                    ]));
                }

                if (!$this->formatAmount($suborder_info['total'])) {
                    continue;
                }

                $charge = $this->chargeCustomer($suborder_info, $customer, $payouts_manager);
                $charges_to_capture[] = $charge;

                fn_update_order_payment_info(
                    $order_id,
                    [
                        'stripe_connect.charge_id' => $charge->id,
                    ]
                );

                if (!$company_id) {
                    // fallback for Vendor debt payout
                    continue;
                }

                if (!empty($suborder_info['use_gift_certificates'])) {
                    continue;
                }

                $withdrawal = $charge->metadata['withdrawal'];
                if (!$withdrawal) {
                    continue;
                }
                $payouts_manager->createWithdrawal($withdrawal, $order_id);


                if (!$this->addon_settings['collect_payouts']) {
                    continue;
                }
                $payouts_manager->acceptPayouts();
            }

            foreach ($charges_to_capture as $charge) {
                $charge->capture();
            }

            $pp_response['order_status'] = 'P';
        } catch (Exception $e) {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = $e->getMessage();

            foreach ($charges_to_capture as $charge) {
                $charge->refund();
            }

            Logger::logException($e);
        }

        if ($customer) {
            $customer->delete();
        }

        return $pp_response;
    }

    /**
     * Gets orders that should be paid.
     *
     * @param array $order Parent order info
     *
     * @return array Keys are order IDs, values are vendors IDs
     */
    protected function getOrdersToCharge(array $order)
    {
        if ($order['status'] === STATUS_PARENT_ORDER) {
            $queue = $this->db->getSingleHash(
                'SELECT order_id, company_id FROM ?:orders WHERE parent_order_id = ?i',
                ['order_id', 'company_id'],
                $order['order_id']
            );
        } else {
            $queue = [
                $order['order_id'] => $order['company_id'],
            ];
        }

        return $queue;
    }

    /**
     * Obtains Stripe account ID to transfer funds to.
     *
     * @param int $company_id Vendor company ID.
     *
     * @return string Stripe account ID
     */
    public static function getChargeReceiver($company_id)
    {
        if (!isset(static::$receivers_cache[$company_id])) {
            if ($company_id) {
                $company_data = fn_get_company_data($company_id);
                static::$receivers_cache[$company_id] = $company_data['stripe_connect_account_id'];
            } else {
                static::$receivers_cache[$company_id] = static::getOwnerAccountId();
            }
        }

        if (empty(static::$receivers_cache[$company_id])) {
            Logger::log(
                Logger::ACTION_FAILURE,
                __('stripe_connect.account_link_error', ['[company_id]' => $company_id])
            );
        }

        return static::$receivers_cache[$company_id];
    }

    /**
     * Formats payment amount by currency.
     *
     * @param float $amount Payment amount
     *
     * @return int Order amount <b>in cents</b>
     */
    protected function formatAmount($amount)
    {
        return $this->price_formatter->asCents($amount, $this->processor_params['currency']);
    }

    /**
     * Calculated application fee that will be excluded from the charge transaction.
     *
     * @param array          $order_info      Order to charge
     * @param PayoutsManager $payouts_manager Configured payouts manager
     * @param \Stripe\Charge $charge          Associated charge
     *
     * @return array Application fee in primary currency and in configured payment method's currency (cents)
     */
    protected function getWithdrawalAmount(array $order_info, PayoutsManager $payouts_manager, Charge $charge = null)
    {
        $application_fee = $payouts_manager->getOrderFee($order_info['order_id']);

        // hold back vendor payouts
        if ($this->addon_settings['collect_payouts']) {
            $application_fee += $payouts_manager->getPendingPayoutsFee();
        }

        $application_fee = min($application_fee, $order_info['total']);

        // the withdrawal that will be displayed in the Accounting
        $accounting_withdrawal = $order_info['total'] - $application_fee;
        $transfer_withdrawal = $accounting_withdrawal;

        if ($charge) {
            $net = $this->getChargeNet($charge);
            // the withdrawal that will be sent as the Transfer to vendor
            $transfer_withdrawal *= $net / $charge->amount;
        }

        $transfer_withdrawal = $this->formatAmount($transfer_withdrawal);
        $application_fee = $this->formatAmount($application_fee);

        return [$accounting_withdrawal, $transfer_withdrawal, $application_fee];
    }

    /**
     * Refunds charge.
     *
     * @param array $order_info Refunded order info
     * @param float $amount     Refunded amount
     *
     * @psalm-param {order_id:int, company_id:int,payment_info:array{'stripe_connect.transfer_id':string,'stripe_connect.charge_id':string}} $order_info Refunded order info
     *
     * @return string|null
     */
    public function refund(array $order_info, $amount)
    {
        $amount = $this->formatAmount($amount);
        $is_vendor_charge_reversed = !empty($order_info['company_id']);
        $refund = null;

        if (!empty($order_info['payment_info']['stripe_connect.transfer_id'])) {
            $transfer = Transfer::retrieve($order_info['payment_info']['stripe_connect.transfer_id']);

            $reversed_transfer_amount = min($transfer->amount, $amount);

            $refund = $transfer->reverse([
                'amount' => $reversed_transfer_amount,
            ]);

            $is_vendor_charge_reversed = false;
        }

        if ($amount) {
            $charge_id = $order_info['payment_info']['stripe_connect.charge_id'];

            $params = [
                'charge' => $charge_id,
                'amount' => $amount,
                'reason' => 'requested_by_customer',
            ];

            // fallback for Vendor debt payout
            $options = null;
            if ($is_vendor_charge_reversed) {
                $receiver = static::getChargeReceiver($order_info['company_id']);
                $options = ['stripe_account' => $receiver];
            }

            $refund = Refund::create($params, $options);
        }

        if ($refund) {
            return $refund->id;
        }

        return null;
    }

    /**
     * Checks that all companies in an order have valid Stripe account.
     *
     * @param array $orders_queue Orders queue
     *
     * @return bool
     */
    protected function validateOrdersQueueReceivers(array $orders_queue)
    {
        $company_id = null;
        try {
            foreach ($orders_queue as $company_id) {
                $account_id = StripeConnect::getChargeReceiver($company_id);
                Account::retrieve($account_id);
            }
        } catch (Exception $e) {
            if ($company_id) {
                Logger::logException($e, [
                    'company_id' => $company_id,
                    'company_name' => fn_get_company_name($company_id)
                ]);
            }
            return false;
        }

        return true;
    }

    /**
     * Gets store's owner account ID.
     *
     * @return null|string Account ID or null if an error occured
     */
    public static function getOwnerAccountId()
    {
        $owner_id = null;
        try {
            $params = static::getProcessorParameters();

            Stripe::setApiKey($params['secret_key']);
            Stripe::setClientId($params['client_id']);
            Stripe::setApiVersion(self::API_VERSION);

            $owner = Account::retrieve();
            $owner_id = $owner->id;
        } catch (Exception $e) {
            Logger::logException($e);
        }

        return $owner_id;
    }

    /**
     * Gets payment intent confirmation details.
     *
     * @param string $payment_intent_payment_method_id Payment method ID
     * @param float  $total                            Payment total
     *
     * @return \Tygh\Common\OperationResult
     */
    public function getPaymentConfirmationDetails($payment_intent_payment_method_id, $total)
    {
        $result = new OperationResult(false);

        $intent_params = [
            'payment_method'      => $payment_intent_payment_method_id,
            'amount'              => $this->formatAmount($total),
            'currency'            => $this->processor_params['currency'],
            'confirmation_method' => 'manual',
            'confirm'             => true,
        ];

        $intent = PaymentIntent::create($intent_params);

        $is_success = in_array($intent->status,
            [
                self::PAYMENT_INTENT_STATUS_REQUIRES_ACTION,
                self::PAYMENT_INTENT_STATUS_SUCCEDED,
            ]);
        $result->setSuccess($is_success);

        $result->setData($intent->id, 'payment_intent_id');

        if ($intent->status === self::PAYMENT_INTENT_STATUS_REQUIRES_ACTION) {
            $result->setData(true, 'requires_confirmation');
            $result->setData($intent->client_secret, 'client_secret');
        }

        if (!$is_success) {
            Logger::log(
                Logger::ACTION_FAILURE,
                __('stripe_connect.unexpected_payment_intent_status', ['[status]' => $intent->status]),
                array_merge([
                    'intent_id' => $intent->id,
                    'status' => $intent->status
                ], $intent_params)
            );
        }

        return $result;
    }

    /**
     * Transfers vendor's withdrawal to his/her Stripe account.
     *
     * @param array                                     $order_info
     * @param \Tygh\Addons\StripeConnect\PayoutsManager $payouts_manager
     * @param \Stripe\Charge                            $charge
     *
     * @return \Stripe\Transfer|null
     *
     * @throws StripeException If the transfers vendor's withdrawal creating failed.
     */
    protected function transferFunds(array $order_info, PayoutsManager $payouts_manager, Charge $charge)
    {
        list($accounting_withdrawal, $transfer_amount) = $this->getWithdrawalAmount($order_info, $payouts_manager, $charge);
        if (!$transfer_amount) {
            return null;
        }

        $receiver = static::getChargeReceiver($order_info['company_id']);
        $description = $this->getWithdrawalDescription($order_info['order_id'], $order_info['company_id']);

        $transfer_params = [
            'currency'           => $this->processor_params['currency'],
            'destination'        => $receiver,
            'amount'             => $transfer_amount,
            'description'        => $description,
            'metadata'           => [
                'order_id'   => $order_info['order_id'],
                'withdrawal' => $accounting_withdrawal,
            ],
            'source_transaction' => $charge->id,
        ];

        try {
            $transfer = Transfer::create($transfer_params);
        } catch (Exception $e) {
            throw new StripeException(
                __('stripe_connect.transfer_creating_error', ['[error]' => $e->getMessage()]),
                $transfer_params
            );
        }

        return $transfer;
    }

    /**
     * Provides description for withdrawal.
     *
     * @param int $order_id
     * @param int $company_id
     *
     * @return string
     */
    protected function getWithdrawalDescription($order_id, $company_id)
    {
        $lang_code = fn_get_company_language($company_id);

        $description = __(
            'stripe_connect.withdrawal_for_the_order',
            [
                '[order_id]' => $order_id,
            ],
            $lang_code
        );

        return $description;
    }

    protected function getChargeByPaymentIntent(PaymentIntent $payment_intent)
    {
        if (!isset($this->charges_cache[$payment_intent->id])) {
            /** @var \Stripe\Charge[] $charges */
            $charges = $payment_intent->charges->all();
            foreach ($charges as $charge) {
                break;
            }
            $this->charges_cache[$payment_intent->id] = $charge;
        }

        return $this->charges_cache[$payment_intent->id];
    }

    protected function getChargeNet(Charge $charge)
    {
        if (!isset($this->nets_cache[$charge->id])) {
            $balance_transaction = BalanceTransaction::retrieve($charge->balance_transaction);
            $this->nets_cache[$charge->id] = $balance_transaction->net;
        }

        return $this->nets_cache[$charge->id];
    }

    /**
     * @param array          $order_info      Order info
     * @param Customer       $customer        Stripe customer
     * @param PayoutsManager $payouts_manager Payouts manager
     *
     * @return Charge
     *
     * @psalm-param array{
     *  total: float,
     *  order_id: int,
     *  company_id: int
     * } $order_info Some order fields
     *
     * @throws StripeException If failed to create a customer' charge.
     */
    protected function chargeCustomer(array $order_info, Customer $customer, PayoutsManager $payouts_manager)
    {
        $amount = $this->formatAmount($order_info['total']);
        $params = [
            'amount'   => $amount,
            'currency' => $this->processor_params['currency'],
            'customer' => $customer->id,
            'metadata' => [
                'order_id'   => $order_info['order_id'],
                'withdrawal' => 0,
            ],
            'capture' => false,
        ];
        $options = null;

        if ($order_info['company_id'] && empty($order_info['use_gift_certificates'])) {
            $receiver = static::getChargeReceiver($order_info['company_id']);
            list($accounting_withdrawal,, $application_fee) = $this->getWithdrawalAmount($order_info, $payouts_manager);
            $params['application_fee'] = $application_fee;
            $params['metadata']['withdrawal'] = $accounting_withdrawal;

            // payment receiver must be specified in options to perform Direct charge
            $options = ['stripe_account' => $receiver];

            // customer account must be shared to a connected account by converting it into a payment token
            $params['source'] = $this->shareCustomer($customer, $receiver);
            unset($params['customer']);
        }

        try {
            $charger = Charge::create($params, $options);
        } catch (Exception $e) {
            throw new StripeException(
                __('stripe_connect.charge_creating_error', ['[error]' => $e->getMessage()]),
                $params
            );
        }

        return $charger;
    }

    /**
     * Creates a customer object to perform charge.
     *
     * @param array $order_info
     *
     * @return \Stripe\Customer
     *
     * @throws StripeException If a Customer object creating failed.
     */
    protected function createCustomer(array $order_info)
    {
        try {
            $customer = Customer::create([
                'email'  => $order_info['email'],
                'source' => $order_info['payment_info']['stripe_connect.token'],
            ]);
        } catch (Exception $e) {
            throw new StripeException(
                __('stripe_connect.customer_creating_error', ['[error]' => $e->getMessage()]),
                [
                    'order_id' => $order_info['order_id'],
                    'email' => $order_info['email'],
                    'payment_info' => $order_info['payment_info']
                ]
            );
        }

        return $customer;
    }

    /**
     * Shares customer to a connected vendor account.
     *
     * @param Customer $customer             Stripe customer
     * @param string   $connected_account_id Connected account identifier
     *
     * @return string Payment token
     *
     * @throws StripeException If failed to share a Stripe customer to a connected vendor account.
     */
    protected function shareCustomer(Customer $customer, $connected_account_id)
    {
        try {
            $token = Token::create(
                ['customer' => $customer->id],
                ['stripe_account' => $connected_account_id]
            );
        } catch (Exception $e) {
            throw new StripeException(
                __('stripe_connect.customer_sharing_error', ['[error]' => $e->getMessage()]),
                [
                    'customer' => $customer->id,
                    'stripe_account' => $connected_account_id
                ]
            );
        }

        return $token->id;
    }
}
