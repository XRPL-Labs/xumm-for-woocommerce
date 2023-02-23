<?php

namespace XummForWoocomerce\XUMM\Callback;

use Xrpl\XummSdkPhp\Response\GetPayload\XummPayload;
use XummForWoocomerce\XUMM\Traits\XummPaymentGatewayTrait;
use XummForWoocomerce\XUMM\Woocommerce\XummPaymentGateway;

/**
 * AbstractHandler Class
 *
 * The default behavior can be implemented inside a base handler class.
 *
 * @since      1.0.0
 * @package    XummForWoocomerce
 * @subpackage XUMM\Callback
 * @author     Andrei R <mdxico@gmail.com>
 */
abstract class AbstractHandler implements Handler
{
    /** @use \XummForWoocommerce\XUMM\Traits\XummPaymentGatewayTrait */
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
