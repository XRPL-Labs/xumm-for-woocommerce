<?php

namespace XummForWoocomerce\XUMM\Callback;

use XummForWoocomerce\XUMM\Facade\Notice;

class SignInHandler extends AbstractHandler
{
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
        }
    }
}
