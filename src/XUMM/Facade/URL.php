<?php

namespace XummForWoocomerce\XUMM\Facade;

use Xrpl\XummSdkPhp\XummSdk;
use XummForWoocomerce\XUMM\Woocommerce\XummPaymentGateway;

class URL
{
    public static function getReturnURL($custom_identifier, $order, XummPaymentGateway $xummPaymentGateway)
    {
        $explorer = $xummPaymentGateway->explorer;

        $sdk = new XummSdk($xummPaymentGateway->api, $xummPaymentGateway->api_secret);
        $payload = $sdk->getPayloadByCustomId($custom_identifier);

        $txid = $payload->response->txid;
        $delivered_amount = $payload->payload->request['Amount'];
        $xr = $payload->customMeta->blob['xr'];

        try {
            $txbody = Transaction::getTransactionDetails($txid);

            if (empty($txbody))
            {
                wc_add_notice(__('Payment failed or cancelled, feel free to try again.', 'xumm-for-woocommerce'), 'error' );
                return $order->get_checkout_payment_url(false);
            }

            $delivered_amount = $txbody['result']['meta']['delivered_amount'];

            if(!Transaction::checkDeliveredAmount($delivered_amount, $order, $xr, $xummPaymentGateway->issuers, $txid, $explorer)) {
                $redirect_url = $order->get_checkout_payment_url(false);
                return $redirect_url;
            } else {
                $order->payment_complete();
                wc_reduce_stock_levels( $order->get_id() );
                $order->add_order_note( __('Hi, your order is paid! Thank you!', 'xumm-for-woocommerce') . '<br>'. __('Check the transaction details', 'xumm-for-woocommerce') .' <a href="'.$explorer.$txid.'">'.__('information', 'xumm-for-woocommerce').'</a>', true );
                WC()->cart->empty_cart();
                return $xummPaymentGateway->get_return_url( $order );
            }
        } catch (\Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            exit();
        }
    }
}
