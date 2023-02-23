<?php

namespace XummForWoocomerce\Constants;

abstract class Config
{
    public const XUMM_DEBUG = TRUE;

    // production = 'https://s1.ripple.com:51234/'
    public const XRPL_HTTP_ADDR = 'https://s.altnet.rippletest.net:51234';

    // production = 'wss://xrpl.ws'
    public const XUMM_WS_ENDPOINT = 'wss://s.altnet.rippletest.net:51233';
}
