<?php

namespace XummForWoocomerce\XUMM\Facade;

use XummForWoocomerce\Constants\Config;

class Transaction
{
    public static function getTransactionDetails($txid)
    {
        $json = json_encode([
            'method' => 'tx',
            'params' => [
                [
                    'transaction' => $txid,
                    'binary' => false
                ]
            ]
        ]);

        $tx = wp_remote_post(Config::XRPL_HTTP_ADDR, array(
            'method'    => 'POST',
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
            'body' => $json
        ));

        if(is_wp_error( $tx )) {
            throw new \Exception(__('Connection error getting payload details from the XUMM platform.', 'xumm-for-woocommerce'));
        }

        return json_decode( $tx['body'], true );
    }

    public static function checkDeliveredAmount($delivered_amount, $order, $issuers, $txid, $explorer)
    {
        $total = $order->get_total();

        if ($delivered_amount != null)
        {
            $type = gettype($delivered_amount);

            switch ($type)
            {
                case 'string':

                    if(!is_numeric($delivered_amount))
                    {
                        throw new \Exception(__('Payment amount error', 'xumm-for-woocommerce'));
                    }

                    // break;
                    $delivered_amount = (float) $delivered_amount;

                case 'float':
                case 'integer':
                    $delivered_amount = $delivered_amount/1000000;

                    if ($delivered_amount < ($total-0.000001)) {
                        if ($delivered_amount == 0)
                        {
                            throw new \Exception(__('No funds received', 'xumm-for-woocommerce'));
                        }
                        else
                        {
                            $order->add_order_note(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' XRP '. number_format($delivered_amount, 6) .'<br>'. __('Open:', 'xumm-for-woocommerce') .' XRP '. number_format(($total - $delivered_amount), 6) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                            throw new \Exception(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce'));
                        }
                    }
                break;

                case 'array':
                    if ($delivered_amount['issuer'] != $issuers)
                    {
                        $order->add_order_note(__('Wrong', 'xumm-for-woocommerce') .'<br>' . __('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                        throw new \Exception(__('The issuer is not the same as the payment, please contact support', 'xumm-for-woocommerce'));
                    }

                    if ($delivered_amount['currency'] != $order->get_currency()) {
                        $order->add_order_note(__('Wrong', 'xumm-for-woocommerce') .'<br>' . __('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                        throw new \Exception(__('The store currency is not the same as the payment, please contact support', 'xumm-for-woocommerce'));
                    }

                    if ($delivered_amount['value'] <= ($total * 0.99))
                    {
                        if ($delivered_amount['value'] == 0)
                        {
                            throw new \Exception(__('No funds received', 'xumm-for-woocommerce'));
                        } else
                        {
                            $order->add_order_note(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br>'. __('Open:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. ($total-$delivered_amount['value']) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                            throw new \Exception(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br>'. __('Open:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. ($total-$delivered_amount['value']) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>');
                        }
                    }
                break;

                default:
                    throw new \Exception(__('Payment amount error', 'xumm-for-woocommerce'));
                break;
            }
        } else
        {
            throw new \Exception(__('Payment amount error', 'xumm-for-woocommerce'));
        }
    }
}
