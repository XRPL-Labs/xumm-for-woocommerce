<?php

namespace XummForWoocomerce\XUMM\Traits;

use XummForWoocomerce\Woocommerce\XummPaymentGateway;

trait XummPaymentGatewayTrait
{
    /**
     * Access to the Xumm Payment Gateway
     *
     * @since    1.0.0
     * @access   protected
     * @var      XummPaymentGateway    $xummGateway    Xumm Payment Gateway
     */
    protected $xummGateway = null;

    public function setXummPaymentGateway(XummPaymentGateway $xummGateway) {
        $this->xummGateway = $xummGateway;
    }

    public function getXummPaymentGateway() : XummPaymentGateway
    {
        return $this->xummGateway;
    }
}
