<?php

namespace Xrpl\XummForWoocommerce\Xumm\Request;

use Xrpl\XummForWoocommerce\Constants\Config;
use Xrpl\XummForWoocommerce\Xumm\Exception\TransactionException;
use Xrpl\XummSdkPhp\Response\Transaction\XrplTransaction;

class TestnetTransactionRequest
{
    public static function doRequest(string $txid) : XrplTransaction
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

        $tx = wp_remote_post('https://s.altnet.rippletest.net:51234', [
            'method'    => 'POST',
            'sslverify' => false,
            'timeout'   => 60,
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
            'body' => $json
        ]);

        if (is_wp_error( $tx ))
        {
            throw new TransactionException;
        }

        $txbody = (array) json_decode($tx['body'], true);
        $result = (array) $txbody['result'];

        $response = new XrplTransaction($txid, Config::TESTNET_XRPL_WS_ENDPOINT, $result);

        return $response;
    }
}
