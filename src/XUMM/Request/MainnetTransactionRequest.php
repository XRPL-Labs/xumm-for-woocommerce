<?php

namespace Xrpl\XummForWoocommerce\XUMM\Request;

use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;
use Xrpl\XummForWoocommerce\XUMM\Exception\TransactionException;
use Xrpl\XummSdkPhp\XummSdk;

class MainnetTransactionRequest
{
    public static function doRequest(string $txid) : array
    {
        $xummGateway = XummPaymentGateway::get_instance();

        try
        {
            $sdk = new XummSdk($xummGateway->api, $xummGateway->api_secret);
            $response = $sdk->getTransaction($txid);

            return json_decode(
                    json_encode($response), true
            );

        } catch (\Exception $e)
        {
            $tx = wp_remote_get('https://data.ripple.com/v2/transactions/'. $txid, [
                'method'    => 'GET',
                'headers'   => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            if (is_wp_error($tx))
            {
                throw new TransactionException;
            }

            return json_decode($tx['body'], true);
        }
    }
}
