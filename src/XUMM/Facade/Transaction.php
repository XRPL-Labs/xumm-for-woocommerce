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

    public static function checkDeliveredAmount($delivered_amount, $order, $xr, $issuers, $txid, $explorer)
    {
        $total = $order->get_total();

        if($delivered_amount != null) {
            switch (gettype($delivered_amount)) {
                case 'string':
                    if(!is_numeric($delivered_amount)) {
                        wc_add_notice(__('Payment amount error', 'xumm-for-woocommerce'), 'error');
                        return false;
                    break;
                    }
                case 'integer':
                    $delivered_amount = $delivered_amount/1000000;
                    $total = $total * $xr;
                    if($delivered_amount < ($total-0.000001)) {
                        if($delivered_amount == 0) wc_add_notice(__('No funds received', 'xumm-for-woocommerce'), 'error');
                        else {
                            wc_add_notice(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce'), 'error' );
                            $order->add_order_note(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' XRP '. number_format($delivered_amount, 6) .'<br>'. __('Open:', 'xumm-for-woocommerce') .' XRP '. number_format(($total - $delivered_amount), 6) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                        }
                        return false;
                    } else return true;
                break;

                case 'array':
                    if($delivered_amount['issuer'] != $issuers) {
                        wc_add_notice( __('The issuer is not the same as the payment, please contact support', 'xumm-for-woocommerce'), 'error' );
                        $order->add_order_note(__('Wrong', 'xumm-for-woocommerce') .'<br>' . __('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                        return false;
                    }

                    if($delivered_amount['currency'] != $order->get_currency()) {
                        wc_add_notice( __('The store currency is not the same as the payment, please contact support', 'xumm-for-woocommerce'), 'error' );
                        $order->add_order_note(__('Wrong', 'xumm-for-woocommerce') .'<br>' . __('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                        return false;
                    }

                    if($delivered_amount['value'] <= ($total * 0.99)) {
                        if($delivered_amount['value'] == 0) wc_add_notice(__('No funds received', 'xumm-for-woocommerce'), 'error');
                        else {
                            wc_add_notice(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce'), 'error');
                            $order->add_order_note(__('Your order is not paid and is less than order total, Please contact support', 'xumm-for-woocommerce') .'<br>'.__('Paid:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br>'. __('Open:', 'xumm-for-woocommerce') .' '. $delivered_amount['currency'] .' '. ($total-$delivered_amount['value']) .'<br>'. '<a href="'.$explorer.$txid.'">'. __('Transaction information', 'xumm-for-woocommerce') .'</a>',true);
                        }
                        return false;
                    }

                    else return true;
                break;

                default:
                    wc_add_notice(__('Payment amount error', 'xumm-for-woocommerce'), 'error');
                    return false;
                break;
            }
        } else {
            wc_add_notice(__('Payment amount error', 'xumm-for-woocommerce'), 'error');
            return false;
        }
    }
}
