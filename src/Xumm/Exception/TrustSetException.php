<?php

namespace Xrpl\XummForWoocommerce\Xumm\Exception;

final class TrustSetException extends \Exception
{
    public function __construct()
    {
        $message = __('Trust line cannot be set, please try again later', 'xumm-for-woocommerce');

        parent::__construct($message);
    }
}
