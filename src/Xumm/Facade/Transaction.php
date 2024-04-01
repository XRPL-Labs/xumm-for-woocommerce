<?php

namespace Xrpl\XummForWoocommerce\Xumm\Facade;

use Xrpl\XummForWoocommerce\Constants\Config;
use Xrpl\XummForWoocommerce\Xumm\Exception\XummErrorException;
use Xrpl\XummForWoocommerce\Xumm\Request\MainnetTransactionRequest;
use Xrpl\XummForWoocommerce\Xumm\Request\TestnetTransactionRequest;
use Xrpl\XummSdkPhp\Response\Transaction\XrplTransaction;

class Transaction
{
    protected const MSG_PAID = 'Paid:';
    protected const MSG_OPEN = 'Open:';
    protected const MSG_ORDER_NOT_PAID_LESS_THAN_TOTAL = 'Your order is not paid and is less ' .
        'than order total, Please contact support';
    protected const MSG_TRANSACTION_INFORMATION = 'Transaction information';

    public static function getTransactionDetails(string $txid) : XrplTransaction
    {
        if (Config::is_mainnet())
        {
            $response = MainnetTransactionRequest::doRequest($txid);
        } else {
            $response = TestnetTransactionRequest::doRequest($txid);
        }

        return $response;
    }

    /**
     * @param mixed $delivered_amount
     * @param bool|\WC_Order|\WC_Order_Refund $order
     * @param string $issuers
     * @param string $txid
     * @param string $explorer
     */
    public static function checkDeliveredAmount(mixed $delivered_amount,
        mixed $order,
        string $issuers,
        string $txid,
        string $explorer
    ): void
    {
        $total = (double) $order->get_total();

        if ($delivered_amount === null) {
            throw new XummErrorException(__('Payment amount error', 'xumm-for-woocommerce'));
        }

        $type = gettype($delivered_amount);

        switch ($type) {
            case 'string':
            case 'float':
            case 'double':
            case 'integer':
                self::checkNumericAmount($delivered_amount, $total, $order, $txid, $explorer);
                break;
            case 'array':
                self::checkArrayAmount($delivered_amount, $total, $order, $issuers, $txid, $explorer);
                break;
            default:
                throw new XummErrorException(__('Payment amount error', 'xumm-for-woocommerce'));
        }
    }

    /**
     * @param mixed $delivered_amount
     * @param float $total
     * @param bool|\WC_Order|\WC_Order_Refund $order
     * @param string $txid
     * @param string $explorer
     */
    private static function checkNumericAmount(mixed $delivered_amount,
        float $total,
        mixed $order,
        string $txid,
        string $explorer
    ): void
    {
        if (!is_numeric($delivered_amount)) {
            throw new XummErrorException(
                __('Payment amount error, the delivered amount is not a number',
                'xumm-for-woocommerce')
            );
        }

        $delivered_amount = $delivered_amount/1000000;

        if ($delivered_amount < ($total-0.000001)) {
            if ($delivered_amount == 0) {
                throw new XummErrorException(__('No funds received', 'xumm-for-woocommerce'));
            }
            $order->add_order_note(
                __(self::MSG_ORDER_NOT_PAID_LESS_THAN_TOTAL,
                'xumm-for-woocommerce') .
                '<br>'.__(self::MSG_PAID, 'xumm-for-woocommerce') .
                ' XRP '. number_format($delivered_amount, 6) .'<br>'.
                __(self::MSG_OPEN, 'xumm-for-woocommerce') .' XRP '.
                number_format(($total - $delivered_amount), 6) .
                '<br>'. '<a href="'.$explorer.$txid.'">' .
                __(self::MSG_TRANSACTION_INFORMATION, 'xumm-for-woocommerce') .'</a>', 1);
            throw new XummErrorException(__(self::MSG_ORDER_NOT_PAID_LESS_THAN_TOTAL, 'xumm-for-woocommerce'));
        }
    }

    /**
     * @param mixed $delivered_amount
     * @param bool|\WC_Order|\WC_Order_Refund $order
     * @param string $issuers
     * @param string $txid
     * @param string $explorer
     */
    private static function checkArrayAmount(mixed $delivered_amount,
        float $total,
        mixed $order,
        string $issuers,
        string $txid,
        string $explorer
    ): void
    {
        if ($delivered_amount['issuer'] != $issuers) {
            $order->add_order_note(
                __('Wrong', 'xumm-for-woocommerce') .'<br>' .
                __(self::MSG_PAID, 'xumm-for-woocommerce') .
                ' '.
                $delivered_amount['currency'] .' '.
                $delivered_amount['value'] .
                '<br> <a href="'.$explorer.$txid.'">'.
                __(self::MSG_TRANSACTION_INFORMATION, 'xumm-for-woocommerce') .
                '</a>', 1);

            throw new XummErrorException(
                __('The issuer is not the same as the payment, please contact support', 'xumm-for-woocommerce'));
        }

        if ($delivered_amount['currency'] != $order->get_currency()) {
            $order->add_order_note(
                __('Wrong', 'xumm-for-woocommerce') .'<br>' .
                __(self::MSG_PAID, 'xumm-for-woocommerce') .' '.
                $delivered_amount['currency'] .' '.
                $delivered_amount['value'] .
                '<br> <a href="'.$explorer.$txid.'">'.
                __(self::MSG_TRANSACTION_INFORMATION, 'xumm-for-woocommerce') .'</a>', 1);

            throw new XummErrorException(
                __('The store currency is not the same as the payment, please contact support',
                'xumm-for-woocommerce')
            );
        }

        $amount_paid = (double) $delivered_amount['value'];

        if ($amount_paid < $total) {
            if ($amount_paid == 0) {
                throw new XummErrorException(__('No funds received', 'xumm-for-woocommerce'));
            }
            $order->add_order_note(
                __(self::MSG_ORDER_NOT_PAID_LESS_THAN_TOTAL, 'xumm-for-woocommerce') .'<br>'.
                __(self::MSG_PAID, 'xumm-for-woocommerce') .' '. $delivered_amount['currency']
                .' '. $amount_paid .'<br>'.
                __(self::MSG_OPEN, 'xumm-for-woocommerce') .' '.
                $delivered_amount['currency'] .' '.
                (double) ($total-$amount_paid) . '<br>' .
                '<a href="'.$explorer.$txid.'">'.
                __(self::MSG_TRANSACTION_INFORMATION, 'xumm-for-woocommerce') .'</a>', 1);

            throw new XummErrorException(
                __(self::MSG_ORDER_NOT_PAID_LESS_THAN_TOTAL, 'xumm-for-woocommerce') .
                '<br>'. __(self::MSG_PAID, 'xumm-for-woocommerce') . ' ' .
                $delivered_amount['currency'] .' '. $amount_paid .'<br>'.
                __(self::MSG_OPEN, 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .
                ' '. ($total-$amount_paid) .'<br>'. '<a href="'.$explorer.$txid.'">'.
                __(self::MSG_TRANSACTION_INFORMATION, 'xumm-for-woocommerce') .'</a>');
        }
    }

}
