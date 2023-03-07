<?php

namespace Xrpl\XummForWoocommerce\Xumm\Callback;

use Xrpl\XummForWoocommerce\Xumm\Exception\SignInException;

/**
 * SignIn Handler Class
 *
 * SignIn Request Handling from XuMM
 *
 * @since      1.0.0
 * @package    Xrpl\XummForWoocommerce
 * @subpackage XUMM\Callback
 * @author     Andrei R <mdxico@gmail.com>
 */
class SignInHandler extends AbstractHandler
{
    /**
     * Handle request
     *
     * @since     1.0.0
     */
    public function handle() : void
    {
        $gateway = $this->getXummPaymentGateway();

        $account = $this->payload->response->account;

        if(!empty($account))
        {
            $gateway->update_option('destination', $account );
            $gateway->update_option('logged_in', true );
            $gateway->logged_in = true;
        } else
        {
            throw new SignInException;
            //throw new \Exception(__('Signing cannot be completed, please try again later', 'xumm-for-woocommerce'));
        }
    }
}
