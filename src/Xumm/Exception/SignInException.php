<?php

namespace Xrpl\XummForWoocommerce\Xumm\Exception;

final class SignInException extends \Exception
{
    public function __construct()
    {
        $message = __('Signing cannot be completed, please try again later', 'xumm-for-woocommerce');

        parent::__construct($message);
    }
}
