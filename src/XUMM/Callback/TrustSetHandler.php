<?php

namespace XummForWoocomerce\XUMM\Callback;

use XummForWoocomerce\XUMM\Facade\Notice;

/**
 * TrustSet Handler Class
 *
 * TrustSet Request Handling from XuMM
 *
 * @since      1.0.0
 * @package    XummForWoocomerce
 * @subpackage XUMM\Callback
 * @author     Andrei R <mdxico@gmail.com>
 */
class TrustSetHandler extends AbstractHandler
{
    /**
     * Handle request
     *
     * @since     1.0.0
     */
    public function handle() : void
    {
        $gateway = $this->getXummPaymentGateway();
        $request = $this->payload->payload->request;

        if (!empty($request['LimitAmount']['issuer']))
        {
            $gateway->update_option('issuer', $request['LimitAmount']['issuer']);

            Notice::add_flash_notice(__('Trust Line Set successfull please check address & test payment', 'xumm-for-woocommerce'));
        }
    }
}
