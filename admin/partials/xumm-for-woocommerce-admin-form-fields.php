<?php

use Xrpl\XummSdkPhp\XummSdk;
use Xrpl\XummForWoocommerce\Constants\Config;

if(!empty($context->api) && !empty($context->api_secret))
{
    $sdk = new XummSdk($context->api, $context->api_secret);
    try {
        $curatedAssets = $sdk->getCuratedAssets();
    } catch (\Exception $e) {
        // Silent is golden
    }
}

$context->form_fields = [
    'xrpl_network' => [
        'title'       => __("XRPL Network", 'xumm-for-woocommerce'),
        'description' => __("Choose the XRPL Network", 'xumm-for-woocommerce'),
        'type'        => 'select',
        'options'     => array(
            'mainnet' => 'Main net',
            'testnet' => 'Test net',
        ),
        'default'     => 'mainnet',
        'desc_tip'    => true
    ],
    'api' => array(
        'title'       => __("API Key", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __('Get the API Key from', 'xumm-for-woocommerce') .' <a href="https://apps.xumm.dev/">https://apps.xumm.dev/</a>',
        'default'     => '',
    ),
    'api_secret' => array(
        'title'       => __("API Secret Key", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __('Get the API Secret Key from', 'xumm-for-woocommerce') .' <a href="https://apps.xumm.dev/">https://apps.xumm.dev/</a>',
        'default'     => '',
    )
];

if (!empty($context->logged_in))
{
    $context->form_fields['destination'] = [
        'title'       => __("XRP Destination address", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __("This is your XRP r Address", 'xumm-for-woocommerce'),
        'desc_tip'    => true,
        'custom_attributes'    => [
            'readonly' => 'readonly'
        ]
    ];

    $context->form_fields['enabled'] = [
        'title'       => __("Enable/Disable", 'xumm-for-woocommerce'),
        'label'       => __("Enable XUMM Gateway", 'xumm-for-woocommerce'),
        'type'        => 'checkbox',
        'description' => "",
        'default'     => 'no'
    ];

    $context->form_fields['title'] = [
        'title'       => __("Title", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __("This is the title which the user sees during checkout", 'xumm-for-woocommerce'),
        'default'     => __("XRP Payment", 'xumm-for-woocommerce'),
        'desc_tip'    => true,
    ];

    $context->form_fields['description'] = [
        'title'       => __("Description", 'xumm-for-woocommerce'),
        'type'        => 'textarea',
        'description' => __("This is the text users will see in the checkout for this payment method", 'xumm-for-woocommerce'),
        'default'     => __("Pay with XRP using the #1 XRPL wallet: XUMM.", 'xumm-for-woocommerce'),
    ];


    $context->form_fields['explorer'] = [
        'title'       => __("Transaction Explorer", 'xumm-for-woocommerce'),
        'description' => __("Choose the explorer to check the transaction", 'xumm-for-woocommerce'),
        'type'        => 'select',
        'options'     => array(
            'https://bithomp.com/explorer/' => 'Bithomp',
            'https://xrpscan.com/tx/' => 'XRPScan',
            'https://livenet.xrpl.org/transactions/' => 'XRPL.org'
        ),
        'default'     => 'https://bithomp.com/explorer/',
        'desc_tip'    => true
    ];

    $context->form_fields['currencies'] = [
        'title'       => __("Select your currency", 'xumm-for-woocommerce'),
        'description' => __("Here you can select how you want to be paid", 'xumm-for-woocommerce'),
        'type'        => 'select',
        'options'     => $context->availableCurrencies
    ];

    if (!empty($curatedAssets->currencies))
    {
        foreach ($curatedAssets->currencies as $v)
        {
            if (get_woocommerce_currency() == $v)
            {
                $context->availableCurrencies[$v] = $v;
            }
        }
        $context->form_fields['currencies']['options'] = $context->availableCurrencies;
    } else {
        $context->form_fields['currencies']['disabled'] = true;
    }

    $context->form_fields['issuers'] = array(
        'title'       => __("Select your issuer", 'xumm-for-woocommerce'),
        'description' => __("Here you can select how you want to be paid", 'xumm-for-woocommerce'),
        'type'        => 'select',
        'options'     => []
    );

    $context->form_fields['issuer'] = [
        'title'       => __("Issuer", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __("This is the issuer that you have been chosen", 'xumm-for-woocommerce'),
        'default'     => '',
        'desc_tip'    => true,
        'class'       => 'hidden'
    ];

    if (!empty ($curatedAssets->details))
    {
        foreach ($curatedAssets->details as $exchange)
        {
            if ($exchange->shortlist === 0) break;

            $exchangeName = $exchange->name;


            foreach ($exchange->currencies as $currency)
            {
                $context->form_fields['issuers']['options'][$currency->issuer] = $exchangeName;
            }
        }
    } else
    {
        $context->form_fields['issuers']['disabled'] = true;
    }
}

$xummObject = !empty($curatedAssets) ? $curatedAssets : new stdClass();

$xummObject->account = $context->destination;
$xummObject->store_currency = get_woocommerce_currency();
$xummObject->ws = Config::get_xrpl_ws_endpoint();
$xummObject->logged_in = $context->logged_in;

wp_localize_script( 'jquery', 'xumm_object', $xummObject);
