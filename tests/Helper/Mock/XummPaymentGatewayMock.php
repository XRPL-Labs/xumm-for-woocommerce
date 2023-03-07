<?php

namespace Xrpl\XummForWoocommerce\Tests\Helper\Mock;

use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;

final class XummPaymentGatewayMock
{
    public static function create(): XummPaymentGateway
    {
        \Mockery::mock('\WC_Payment_Gateway');

        $gateway = \Mockery::mock('Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway');
        $gateway->shouldReceive('update_option');

        return $gateway;
    }
}
