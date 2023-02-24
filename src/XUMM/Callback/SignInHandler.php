<?php

namespace XummForWoocomerce\XUMM\Callback;

use XummForWoocomerce\XUMM\Facade\Notice;

/**
 * SignIn Handler Class
 *
 * SignIn Request Handling from XuMM
 *
 * @since      1.0.0
 * @package    XummForWoocomerce
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

            Notice::add_flash_notice(__('Sign In successfull please check address & test payment', 'xumm-for-woocommerce'));
        } else
        {
            Notice::add_flash_notice(__('Signing cannot be completed, please try again later', 'xumm-for-woocommerce'), 'error');
        }
    }
}
