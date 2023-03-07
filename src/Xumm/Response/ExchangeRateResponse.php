<?php

namespace Xrpl\XummForWoocommerce\Xumm\Response;

class ExchangeRateResponse
{
    public float $totalSum;
    public float $xr;

    public function __construct(
        float $totalSum,
        float $xr
    ) {
        $this->totalSum = $totalSum;
        $this->xr = $xr;
    }
}
