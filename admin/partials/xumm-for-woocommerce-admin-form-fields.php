<?php

use Xrpl\XummSdkPhp\XummSdk;

if(!empty($context->api) && !empty($context->api_secret)) 
{
    $sdk = new XummSdk($context->api, $context->api_secret);
    $curatedAssets = $sdk->getCuratedAssets();
}

$context->form_fields = [
    'enabled' => [
        'title'       => __("Enable/Disable", 'xumm-for-woocommerce'),
        'label'       => __("Enable XUMM Gateway", 'xumm-for-woocommerce'),
        'type'        => 'checkbox',
        'description' => "",
        'default'     => 'no'
    ],
    'title' => array(
        'title'       => __("Title", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __("This is the title which the user sees during checkout", 'xumm-for-woocommerce'),
        'default'     => __("XRP Payment", 'xumm-for-woocommerce'),
        'desc_tip'    => true,
    ),
    'description' => array(
        'title'       => __("Description", 'xumm-for-woocommerce'),
        'type'        => 'textarea',
        'description' => __("This is the text users will see in the checkout for this payment method", 'xumm-for-woocommerce'),
        'default'     => __("Pay with XRP using the #1 XRPL wallet: XUMM.", 'xumm-for-woocommerce'),
    ),
    'destination' => array(
        'title'       => __("XRP Destination address", 'xumm-for-woocommerce'),
        'type'        => 'text',
        'description' => __("This is your XRP r Address", 'xumm-for-woocommerce'),
        'desc_tip'    => true,
    ),
    'explorer' => array(
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
    ),
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

$context->form_fields['currencies'] = array(
    'title'       => __("Select your currency", 'xumm-for-woocommerce'),
    'description' => __("Here you can select how you want to be paid", 'xumm-for-woocommerce'),
    'type'        => 'select',
    'options'     => $context->availableCurrencies
);

if (!empty ($curatedAssets->currencies) ) {
    $context->availableCurrencies['XRP'] = 'XRP';
    foreach ($curatedAssets->currencies as $v) {
        if(get_woocommerce_currency() == $v){
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

if (!empty ($curatedAssets->details) && get_woocommerce_currency() != 'XRP') {
    foreach ($curatedAssets->details as $exchange) {
        if ($exchange['shortlist'] === 0) break;

        $exchangeName = $exchange['name'];
        foreach ($exchange['currencies'] as $currency) {
            $value = $currency['issuer'];
            $context->form_fields['issuers']['options'][$value] = $exchangeName;
        }
    }
} else {
    $context->form_fields['issuers']['disabled'] = true;
}

$curatedAssets->account = $context->destination;
$curatedAssets->store_currency = get_woocommerce_currency();

wp_localize_script( 'jquery', 'xumm_object', $curatedAssets);