<?php

namespace XummForWoocomerce\XUMM\Callback;

use Xrpl\XummSdkPhp\Response\GetPayload\XummPayload;
use XummForWoocomerce\XUMM\Traits\XummPaymentGatewayTrait;
use XummForWoocomerce\XUMM\Woocommerce\XummPaymentGateway;

abstract class AbstractHandler implements Handler
{
    use XummPaymentGatewayTrait;

    protected XummPayload $payload;

    public function __construct(XummPaymentGateway $gateway, XummPayload $payload)
    {
        $this->setXummPaymentGateway($gateway);
        $this->payload = $payload;
    }

    public function handle(): void
    {

    }
}
