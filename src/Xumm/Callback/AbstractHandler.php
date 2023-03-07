<?php

namespace Xrpl\XummForWoocommerce\Xumm\Callback;

use Xrpl\XummSdkPhp\Response\GetPayload\XummPayload;
use Xrpl\XummForWoocommerce\Xumm\Traits\XummPaymentGatewayTrait;
use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;

/**
 * AbstractHandler Class
 *
 * The default behavior can be implemented inside a base handler class.
 *
 * @since      1.0.0
 * @package    Xrpl\XummForWoocommerce
 * @subpackage XUMM\Callback
 * @author     Andrei R <mdxico@gmail.com>
 */
abstract class AbstractHandler implements Handler
{
    /** @use \Xrpl\XummForWoocommerce\Xumm\Traits\XummPaymentGatewayTrait */
    use XummPaymentGatewayTrait;

    /**
     * @var XummPayload
     */
    protected XummPayload $payload;

    /**
     * Construct method
     *
     * @since     1.0.0
     */
    public function __construct(XummPaymentGateway $gateway, XummPayload $payload)
    {
        $this->setXummPaymentGateway($gateway);
        $this->payload = $payload;
    }

    /**
     * Default request handling
     *
     * @since     1.0.0
     */
    public function handle(): void
    {
        //
    }
}
