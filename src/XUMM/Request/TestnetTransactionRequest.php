<?php

namespace Xrpl\XummForWoocommerce\XUMM\Request;

use Xrpl\XummForWoocommerce\XUMM\Exception\TransactionException;

class TestnetTransactionRequest
{
    public static function doRequest(string $txid) : array
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

        $tx = wp_remote_post('https://s.altnet.rippletest.net:51234', array(
            'method'    => 'POST',
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
            'body' => $json
        ));

        if (is_wp_error( $tx ))
        {
            throw new TransactionException;
        }

        return json_decode($tx['body'], true);
    }
}
