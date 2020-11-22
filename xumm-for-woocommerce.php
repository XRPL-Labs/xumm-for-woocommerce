<?php
/**
 * Plugin Name: XUMM payments for WooCommerce
 * Plugin URI:  https://github.com/KoenPaas/xumm-for-woocommerce
 * Description: Make XRP payments using XUMM
 * Author:      xumm
 * Author URI:  https://xumm.app/
 * Version:     0.3.1
 * License:     GPL v2 or later
 * License URI: https://xrpl-labs.com/static/documents/XRPL-Labs-Terms-of-Service-V1.pdf
 */

require 'inc/language.php';

function init_xumm_gateway_class() {
    global $lang;

    class WC_Gateway_XUMM_Gateway extends WC_Payment_Gateway {
        public $endpoint = 'https://xumm.app/api/v1/platform/';

        public $availableCurrencies = [];

        public function __construct() {
            global $lang;

            $this->id = 'xumm';
            $this->icon = plugin_dir_url(__FILE__).'public/images/label.svg';
            $this->has_fields = false;
            $this->method_title = $lang->construct->title;
            $this->method_description = $lang->construct->description;

            $this->supports = array(
                'products'
            );

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

            wp_register_script( 'xumm_js', plugins_url( 'xumm-for-woocommerce/js/admin.js' , plugin_dir_path( __FILE__ )), array('jquery'), null, false );

            $this->init_form_fields();
            $this->init_settings();

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            add_action( 'woocommerce_api_'. $this->id, array( $this, 'callback_handler' ));
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_script' ));

        }

        public function enqueue_admin_script($hook) {
            if ( 'woocommerce_page_wc-settings' != $hook ) {
                return;
            }
            wp_enqueue_script('xumm_js');
        }


        public function init_form_fields() {
            require 'inc/admin_form.php';
        }

        public function admin_options() {
            require 'inc/admin_options.php';
        }

        public function payment_fields() {
            echo wpautop( wp_kses_post( $this->description ) );
        }

        public function process_payment( $order_id ) {
            $return = require 'inc/process_payment.php';
            if($return != null){
                return array(
                    'result' => 'success',
                    'redirect' => $return
                );
            } else {
                return;
            }
        }

        public function callback_handler() {
            global $lang;
            require 'inc/validate.php';

            if(!empty($_GET["order_id"])) {
                $status = $lang->callback->status;

                $custom_identifier = sanitize_text_field($_GET["order_id"]);
                $order_id = explode("_", $custom_identifier)[0];
                $order = wc_get_order( $order_id );

                $order_status  = $order->get_status();
                switch ($order_status) {
                    case 'processing':
                        wc_add_notice($status->processing);
                        $redirect_url = $this->get_return_url( $order );
                        break;
                    case 'pending':
                        $redirect_url = getReturnUrl($custom_identifier, $order, $this);
                        break;
                    case 'on-hold':
                        wc_add_notice($status->on_hold);
                        $redirect_url = $order->get_checkout_payment_url(false);
                        break;
                    case 'completed':
                        wc_add_notice($status->completed, 'success');
                        $redirect_url = $this->get_return_url( $order );
                        break;
                    case 'cancelled':
                        wc_add_notice($status->cancelled, 'error' );
                        $redirect_url = $order->get_checkout_payment_url(false);
                        break;
                    case 'failed':
                        wc_add_notice($status->failed, 'error' );
                        $redirect_url = $order->get_checkout_payment_url(false);
                        break;
                    case 'refunded':
                        wc_add_notice($status->refunded, 'notice' );
                        $redirect_url = $this->get_return_url( $order );
                        break;
                    default:
                        wc_add_notice($status->default, 'error' );
                        wp_safe_redirect($order->get_checkout_payment_url(false));
                        break;
                }
                wp_safe_redirect($redirect_url);
            }

            require 'inc/callback.php';
        }

    }
}

if(class_exists('WooCommerce')) {
    add_action( 'plugins_loaded', 'init_xumm_gateway_class' );

    function add_xumm_gateway_class( $methods ) {
        $methods[] = 'WC_Gateway_XUMM_Gateway'; 
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_xumm_gateway_class' );

    add_filter( 'woocommerce_currencies', 'add_xrp_currency' );
    function add_xrp_currency( $xrp_currency ) {
        $xrp_currency['XRP'] = __( 'XRP', 'woocommerce' );
        $xrp_currency['ETH'] = __( 'Ethereum', 'woocommerce' );
        return $xrp_currency;
    }

    add_filter('woocommerce_currency_symbol', 'add_xrp_currency_symbol', 10, 2);
    function add_xrp_currency_symbol( $custom_currency_symbol, $custom_currency ) {
        switch( $custom_currency ) {
            case 'XRP': $custom_currency_symbol = 'XRP '; break;
            case 'ETH': $custom_currency_symbol = 'Ξ'; break;
        }
        return $custom_currency_symbol;
    }

    add_filter( 'woocommerce_available_payment_gateways', 'dissable_xumm' );
    function dissable_xumm($available_gateways) {
        $xumm = new WC_Gateway_XUMM_Gateway;
        $storeCurrency = get_woocommerce_currency();
        
        if (empty($xumm->api) || empty($xumm->api_secret)) unset($available_gateways['xumm']);
        if (!in_array($storeCurrency, $xumm->availableCurrencies)) unset($available_gateways['xumm']);
        if ($storeCurrency != 'XRP' && $xumm->currencies != 'XRP' && $storeCurrency != $xumm->currencies) unset($available_gateways['xumm']);
        if ($xumm->currencies != 'XRP' && empty($xumm->issuers)) unset($available_gateways['xumm']);
        return $available_gateways;
    }
}

?>