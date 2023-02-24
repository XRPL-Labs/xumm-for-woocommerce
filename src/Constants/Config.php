<?php

namespace XummForWoocomerce\Constants;

abstract class Config
{
    // public const XRPL_HTTP_ADDR = 'https://s1.ripple.com:51234/' // mainnet
    public const XRPL_HTTP_ADDR = 'https://s.altnet.rippletest.net:51234'; // testnet

    // public const XUMM_WS_ENDPOINT = 'wss://xrpl.ws' // mainnet
    public const XUMM_WS_ENDPOINT = 'wss://s.altnet.rippletest.net:51233'; // testnet
}
