<?php

namespace Xrpl\XummForWoocommerce\Xumm\Request;

use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;
use Xrpl\XummForWoocommerce\Xumm\Exception\TransactionException;
use Xrpl\XummSdkPhp\Response\Transaction\XrplTransaction;
use Xrpl\XummSdkPhp\XummSdk;

class MainnetTransactionRequest
{
    public static function doRequest(string $txid) : XrplTransaction
    {
        $xummGateway = XummPaymentGateway::get_instance();

        try {
            $sdk = new XummSdk($xummGateway->api, $xummGateway->api_secret);
            $response = $sdk->getTransaction($txid);

            if ($response->transaction['meta']['TransactionResult'] != 'tesSUCCESS')
            {
                throw new TransactionException;
            }

        } catch (\Exception $e)
        {
            throw new TransactionException;
        }

        return $response;
    }
}
