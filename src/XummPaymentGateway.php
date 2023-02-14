<?php

namespace XummForWoocomerce;

class XummPaymentGateway extends \WC_Payment_Gateway 
{
    public $endpoint = 'https://xumm.app/api/v1/platform/';

    public $availableCurrencies = [];

    public function __construct() 
    {
        global $lang;

        $this->id = 'xumm';
        $this->icon = plugin_dir_url(__FILE__).'public/images/label.svg';
        $this->has_fields = false;
        $this->method_title = "Accept XUMM payments";
        $this->method_description = "Receive any supported currency into your XRP account using XUMM";

        $this->supports = ['products'];

        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->destination = $this->get_option('destination');
        $this->currency = $this->get_option('currency');
        $this->issuer = $this->get_option('issuer');
        $this->explorer = $this->get_option('explorer');
        $this->api = $this->get_option('api');
        $this->api_secret = $this->get_option('api_secret');
        $this->currencies = $this->get_option('currencies');
        $this->issuers = $this->get_option('issuers');

        $this->init_form_fields();
        $this->init_settings();
    }

    public function init_form_fields() 
    {
        apply_filters('xumm_init_form_fields', $this);
    }

    public function admin_options() 
    {     
        apply_filters('xumm_display_plugin_options', $this);
    }
}