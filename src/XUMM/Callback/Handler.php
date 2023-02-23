<?php

namespace XummForWoocomerce\XUMM\Callback;

use Xrpl\XummSdkPhp\Response\GetPayload\XummPayload;
use XummForWoocomerce\XUMM\Woocommerce\XummPaymentGateway;

interface Handler
{
    public function __construct(XummPaymentGateway $gateway, XummPayload $payload);

    public function handle(): void;
}
