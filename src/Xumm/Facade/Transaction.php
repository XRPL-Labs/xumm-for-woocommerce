<?php

namespace Xrpl\XummForWoocommerce\Xumm\Facade;

use Xrpl\XummForWoocommerce\Constants\Config;
use Xrpl\XummForWoocommerce\Xumm\Request\MainnetTransactionRequest;
use Xrpl\XummForWoocommerce\Xumm\Request\TestnetTransactionRequest;
use Xrpl\XummSdkPhp\Response\Transaction\XrplTransaction;

class Transaction
{
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
    public static function checkDeliveredAmount(mixed $delivered_amount, mixed $order, string $issuers, string $txid, string $explorer) : void
    {
        $total = (double) $order->get_total();

        if ($delivered_amount != null)
        {
            $type = gettype($delivered_amount);

            switch ($type)
            {
                case 'string':
                case 'float':
                case 'double':
                case 'integer':

                    if (!is_numeric($delivered_amount))
                    {
                        throw new \Exception(__('Payment amount error, the delivered amount is not a number', 'xumm-for-woocommerce'));
                    }

                    $delivered_amount = $delivered_amount/1000000;

                    if ($delivered_amount < ($total-0.000001)) {
                        if ($delivered_amount == 0)
                        {
                            throw new \Exception(__('No funds received', 'xumm-for-woocommerce'));
                        }
                        else
                        {
                            $order->add_order_note(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' XRP '. number_format($delivered_amount, 6) .'<br>'. __('Open:', 'xumm-for-woocommerce') .' XRP '. number_format(($total - $delivered_amount), 6) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>', 1);
                            throw new \Exception(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce'));
                        }
                    }
                break;

                case 'array':
                    if ($delivered_amount['issuer'] != $issuers)
                    {
                        $order->add_order_note(__('Wrong', 'xumm-for-woocommerce') .'<br>' . __('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>', 1);
                        throw new \Exception(__('The issuer is not the same as the payment, please contact support', 'xumm-for-woocommerce'));
                    }

                    if ($delivered_amount['currency'] != $order->get_currency())
                    {
                        $order->add_order_note(__('Wrong', 'xumm-for-woocommerce') .'<br>' . __('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>', 1);
                        throw new \Exception(__('The store currency is not the same as the payment, please contact support', 'xumm-for-woocommerce'));
                    }

                    $amount_paid = (double) $delivered_amount['value'];

                    if ($amount_paid < $total)
                    {
                        if ($amount_paid == 0)
                        {
                            throw new \Exception(__('No funds received', 'xumm-for-woocommerce'));
                        } else
                        {
                            $order->add_order_note(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $amount_paid .'<br>'. __('Open:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. (double) ($total-$amount_paid) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>', 1);
                            throw new \Exception(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $amount_paid .'<br>'. __('Open:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. ($total-$amount_paid) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>');
                        }
                    }

                break;

                default:
                    throw new \Exception(__('Payment amount error', 'xumm-for-woocommerce'));
                // break;
            }
        } else
        {
            throw new \Exception(__('Payment amount error', 'xumm-for-woocommerce'));
        }
    }
}
