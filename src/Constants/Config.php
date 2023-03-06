<?php

namespace Xrpl\XummForWoocommerce\Constants;

use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;

abstract class Config
{
    public const MAINNET_XRPL_WS_ENDPOINT = 'wss://xrpl.ws';
    public const TESTNET_XRPL_WS_ENDPOINT = 'wss://s.altnet.rippletest.net:51233';

    public static function is_mainnet() : bool
    {
        if (class_exists('Woocommerce')) {
            return XummPaymentGateway::get_instance()->xrpl_network == 'mainnet';
        }

        return false;
    }

    public static function get_xrpl_ws_endpoint() : string
    {
        if (self::is_mainnet())
        {
            return self::MAINNET_XRPL_WS_ENDPOINT;
        }

        return self::TESTNET_XRPL_WS_ENDPOINT;
    }
}
