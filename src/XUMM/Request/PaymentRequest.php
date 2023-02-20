<?php

namespace XummForWoocomerce\XUMM\Request;

use XummForWoocomerce\XUMM\Woocommerce\XummPaymentGateway;

class PaymentRequest
{
    /**
     * Access to the Xumm Payment Gateway
     *
     * @since    1.0.0
     * @access   protected
     * @var      XummPaymentGateway    $xummGateway    Xumm Payment Gateway
     */
    protected $xummGateway;

    public function setXummPaymentGateway(XummPaymentGateway $xummGateway) {
        $this->xummGateway = $xummGateway;
    }

    public function getXummPaymentGateway() : XummPaymentGateway
    {
        return $this->xummGateway;
    }

    public function processPayment($orderId)
    {
        $context = $this->getXummPaymentGateway();
        $order = wc_get_order( $orderId );
        $storeCurrency = get_woocommerce_currency();
        $exchange_rates_url = 'https://data.ripple.com/v2/exchange_rates/';

        //Check against that XRP cannot have an issuer and if trustlines are available in what is used in the
        if($storeCurrency == 'XRP' && $context->currencies != 'XRP') {
            $apiCall = $exchange_rates_url . $storeCurrency .'/'. $context->currencies .'+'. $context->issuers;
        } else if ($storeCurrency != 'XRP' && $context->currencies == 'XRP') {
            $apiCall = 'https://www.bitstamp.net/api/v2/ticker_hour/' . $context->currencies . $storeCurrency;
            $apiCall = strtolower($apiCall);
        } else if ($storeCurrency == 'XRP' && $context->currencies == 'XRP') {
            $apiCall = null;
            $xr = 1;
        } else if ($storeCurrency == $context->currencies) {
            $apiCall = null;
            $xr = 1;
        } else if ($storeCurrency != 'XRP' && $context->currencies != 'XRP' && $storeCurrency != $context->currencies) {
            wc_add_notice(__('Currency pair not supported. Please check the store currency & settings in the XUMM payment setting in WooCommerce', 'xumm-for-woocommerce'), 'error');
            return;
        } else {
            wc_add_notice(__('Currency issue', 'xumm-for-woocommerce'), 'error' );
            return;
        }

        if(!is_null($apiCall)) {
            $response = wp_remote_get($apiCall);
            $body = json_decode( $response['body'], true );

            preg_match('@^(?:https://)?([^/]+)@i', $apiCall, $matches);
            $host = $matches[1];
            switch ($host) {
                case 'www.bitstamp.net':
                    $xr = 1 / $body['ask'];
                    $totalSum = $order->get_total() * $xr;
                    break;
                case 'data.ripple.com':
                    $xr = $body['rate'];
                    $totalSum = $order->get_total() * $xr;
                    break;
                default:
                    $xr = null;
                    $totalSum = $order->get_total();
                    break;
            }
        } else {
            $totalSum = $order->get_total();
        }

        $identifier = $orderId . '_' . strtoupper(substr(md5(microtime()), 0, 10));

        $totalSum = round($totalSum, 6);
        $query = array(
            'wc-api' => 'XUMM',
            'order_id' => $identifier
        );

        $return_url = add_query_arg($query, get_home_url());

        $headers = array(
            'Content-Type' => 'application/json',
            'X-API-Key' => $context->api,
            'X-API-Secret' => $context->api_secret
        );

        $memo = bin2hex('Order id: '.$orderId.', '.__('paid with XUMM', 'xumm-for-woocommerce'));

        $body = [
            'txjson'  => array(
                'TransactionType' => 'Payment',
                'Destination' => $context->destination,
                'Memos' => array(
                    0 => array(
                        'Memo' => array(
                            'MemoType' => strtoupper(bin2hex(__('https://xumm.app/', 'xumm-for-woocommerce'))),
                            'MemoData' => strtoupper($memo)
                        )
                    )
                ),
                'Flags' => 2147483648
            ),
            'options' => array(
                'submit' => 'true',
                'expire' => 15,
                'return_url' => array(
                    'web' => $return_url
                )
            ),
            'custom_meta' => array(
                'identifier' => $identifier,
                'blob' => array(
                    'xr' => $xr,
                    'base' => $storeCurrency
                )
            )
        ];

        error_log($totalSum);
        if ($context->currencies === 'XRP') {
            $body['txjson']['Amount'] = number_format($totalSum * 1000000, 0, '', '');
        } else {
            $body['txjson']['Amount'] = array(
                'currency' => $context->currencies,
                'value' => $totalSum,
                'issuer' => $context->issuers
            );
        }

        if (wp_is_mobile()) {
            $body['options']['return_url']['app'] = $return_url;
        }

        $body = wp_json_encode($body);
        error_log(print_r($body, true));

        $response = wp_remote_post('https://xumm.app/api/v1/platform/payload', array(
            'method'    => 'POST',
            'headers'   => $headers,
            'body'      => $body
            )
        );

        if (!is_wp_error($response) ) {
            $body = json_decode($response['body'], true );
            if ($body['next']['always'] != null) {
            // Redirect to the XUMM processor page
                return $body['next']['always'];
            } else {
                wc_add_notice(__('Got an error from XUMM', 'xumm-for-woocommerce'), 'error');
                return;
            }
        } else {
            wc_add_notice(__('Connection error', 'xumm-for-woocommerce'), 'error');
            return;
        }
    }
}
