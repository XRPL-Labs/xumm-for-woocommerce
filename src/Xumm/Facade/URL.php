<?php

namespace Xrpl\XummForWoocommerce\Xumm\Facade;

use Xrpl\XummSdkPhp\XummSdk;
use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;

class URL
{
    public static function getReturnURL(string $custom_identifier, object $order, XummPaymentGateway $xummPaymentGateway) : string
    {
        $explorer = $xummPaymentGateway->explorer;

        $sdk = new XummSdk($xummPaymentGateway->api, $xummPaymentGateway->api_secret);
        $payload = $sdk->getPayloadByCustomId($custom_identifier);

        $txid = $payload->response->txid;

        try
        {
            if ($payload->response->dispatchedResult != 'tesSUCCESS')
            {
                throw new \Exception(__('Payment failed or cancelled, feel free to try again.', 'xumm-for-woocommerce'));
            }

            $transaction = Transaction::getTransactionDetails($txid);

            $delivered_amount = $transaction->transaction['meta']['delivered_amount'];

            Transaction::checkDeliveredAmount($delivered_amount, $order, $xummPaymentGateway->issuers, $txid, $explorer);

            $order->payment_complete();
            wc_reduce_stock_levels( $order->get_id() );
            $order->add_order_note( __('Hi, your order is paid! Thank you!', 'xumm-for-woocommerce') . '<br>'. __('Check the transaction details', 'xumm-for-woocommerce') .' <a href="'.$explorer.$txid.'">'.__('information', 'xumm-for-woocommerce').'</a>', true );
            WC()->cart->empty_cart();

            return $xummPaymentGateway->get_return_url( $order );

        } catch (\Exception $e)
        {
            wc_add_notice($e->getMessage(), 'error');
            return $order->get_checkout_payment_url(false);
        }
    }
}
