<?php

namespace XummForWoocomerce\XUMM\Callback;

use Xrpl\XummSdkPhp\Response\GetPayload\XummPayload;
use XummForWoocomerce\Woocommerce\XummPaymentGateway;

/**
 * Handler Class
 *
 * The Handler interface declares a method for building postback from Xumm
 * It also declares a method for handle request
 *
 * @since      1.0.0
 * @package    XummForWoocomerce
 * @subpackage XUMM\Callback
 * @author     Andrei R <mdxico@gmail.com>
 */
interface Handler
{
    public function __construct(XummPaymentGateway $gateway, XummPayload $payload);
    public function handle(): void;
}
