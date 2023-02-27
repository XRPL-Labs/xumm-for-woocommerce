<?php

namespace XummForWoocomerce\Constants;

use XummForWoocomerce\Woocommerce\XummPaymentGateway;

abstract class Config
{
    public const MAINNET_XRPL_HTTP_ADDR = 'https://s1.ripple.com:51234';
    public const MAINNET_XRPL_WS_ENDPOINT = 'wss://xrpl.ws';

    public const TESTNET_XRPL_HTTP_ADDR = 'https://s.altnet.rippletest.net:51234';
    public const TESTNET_XRPL_WS_ENDPOINT = 'wss://s.altnet.rippletest.net:51233';

    private static function is_mainnet()
    {
        if (class_exists('Woocommerce')) {
            return XummPaymentGateway::getInstance()->xrpl_network == 'mainnet';
        }

        return false;
    }

    public static function get_xrpl_http_addr()
    {
        if (self::is_mainnet())
        {
            return self::MAINNET_XRPL_HTTP_ADDR;
        }

        return self::TESTNET_XRPL_HTTP_ADDR;
    }

    public static function get_xrpl_ws_endpoint()
    {
        if (self::is_mainnet())
        {
            return self::MAINNET_XRPL_WS_ENDPOINT;
        }

        return self::TESTNET_XRPL_WS_ENDPOINT;
    }
}
