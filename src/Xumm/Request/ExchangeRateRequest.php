<?php

namespace Xrpl\XummForWoocommerce\Xumm\Request;

use Xrpl\XummForWoocommerce\Xumm\Response\ExchangeRateResponse;
use Xrpl\XummForWoocommerce\Xumm\Traits\XummPaymentGatewayTrait;

class ExchangeRateRequest
{
    use XummPaymentGatewayTrait;

    /**
     * @param string $storeCurrency
     * @param mixed $orderTotal
     */
    public function getExchangeRates(string $storeCurrency, mixed $orderTotal) : ExchangeRateResponse
    {
        $xr = null;
        $context = $this->getXummPaymentGateway();

        $exchangeRatesUrl = 'https://data.ripple.com/v2/exchange_rates/';

        //Check against that XRP cannot have an issuer and if trustlines are available in what is used in the
        if($storeCurrency == 'XRP' && $context->currencies != 'XRP') {
            $apiCall = $exchangeRatesUrl . $storeCurrency .'/'. $context->currencies .'+'. $context->issuers;
        } else if ($storeCurrency != 'XRP' && $context->currencies == 'XRP') {
            $apiCall = 'https://www.bitstamp.net/api/v2/ticker_hour/' . $context->currencies . $storeCurrency;
            $apiCall = strtolower($apiCall);
        } else if ($storeCurrency == 'XRP' && $context->currencies == 'XRP') {
            $apiCall = null;
            $xr = 1;
        } else if ($storeCurrency == $context->currencies) {
            $apiCall = null;
            $xr = 1;
        } else if ($storeCurrency != 'XRP' && $context->currencies != 'XRP' && $storeCurrency != $context->currencies) {
            throw new \Exception(__('Currency pair not supported. Please check the store currency & settings in the XUMM payment setting in WooCommerce', 'xumm-for-woocommerce'));
        } else {
            throw new \Exception(__('Currency issue', 'xumm-for-woocommerce'));
        }

        if (!is_null($apiCall)) {
            $response = wp_remote_get($apiCall);
            $body = (array) json_decode($response['body'], true);

            preg_match('@^(?:https://)?([^/]+)@i', $apiCall, $matches);
            $host = $matches[1];
            switch ($host) {
                case 'www.bitstamp.net':
                    $xr = 1 / $body['ask'];
                    $totalSum = $orderTotal * $xr;
                    break;
                case 'data.ripple.com':
                    $xr = $body['rate'];
                    $totalSum = $orderTotal * $xr;
                    break;
                default:
                    $xr = null;
                    $totalSum = $orderTotal;
                    break;
            }
        } else {
            $totalSum = $orderTotal;
        }

        return new ExchangeRateResponse($totalSum, $xr);
    }
}
