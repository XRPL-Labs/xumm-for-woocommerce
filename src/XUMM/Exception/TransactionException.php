<?php

namespace Xrpl\XummForWoocommerce\XUMM\Exception;

final class TransactionException extends \Exception
{
    public function __construct()
    {
        $message = __('Connection error getting payload details from the XUMM platform.', 'xumm-for-woocommerce');

        parent::__construct($message);
    }
}
