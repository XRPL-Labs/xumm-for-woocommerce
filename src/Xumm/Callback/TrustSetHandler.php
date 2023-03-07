<?php

namespace Xrpl\XummForWoocommerce\Xumm\Callback;

use Xrpl\XummForWoocommerce\Xumm\Exception\TrustSetException;

/**
 * TrustSet Handler Class
 *
 * TrustSet Request Handling from XuMM
 *
 * @since      1.0.0
 * @package    Xrpl\XummForWoocommerce
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

        if (!empty($this->payload->response->dispatchedResult)
            &&
            $this->payload->response->dispatchedResult == 'tesSUCCESS')
        {
            $gateway->update_option('currencies', $request['LimitAmount']['currency']);
            $gateway->update_option('currency', $request['LimitAmount']['currency']);
            $gateway->update_option('issuer', $request['LimitAmount']['issuer']);


        } else
        {
            throw new TrustSetException;
        }
    }
}
