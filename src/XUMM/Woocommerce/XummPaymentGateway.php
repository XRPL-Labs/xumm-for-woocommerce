<?php

namespace XummForWoocomerce\XUMM\Woocommerce;

use Xrpl\XummSdkPhp\XummSdk;
use XummForWoocomerce\Constants\Config;
use XummForWoocomerce\XUMM\Facade\URL;
use XummForWoocomerce\XUMM\Request\PaymentRequest;
class XummPaymentGateway extends \WC_Payment_Gateway
{
    public $endpoint = 'https://xumm.app/api/v1/platform/';

    public $availableCurrencies = [
        'XRP' => 'XRP',
        'USD' => 'USD',
        'EUR' => 'EUR'
    ];

    public $currencies;
    public $enabled;
    public $id;
    public $has_fields;
    public $method_title;
    public $method_description;
    public $supports;
    public $title;
    public $description;
    public $destination;
    public $issuer;
    public $explorer;
    public $api;
    public $api_secret;
    public $issuers;
    public $logged_in;
    public $currency;


    public function __construct()
    {
        $this->id = 'xumm';
        $this->icon = xumm_plugin_url() . 'admin/public/images/label.svg';
        $this->has_fields = false;
        $this->method_title = __("Accept XUMM payments", "xumm-for-woocommerce");
        $this->method_description = __("Receive any supported currency into your XRP account using XUMM", "xumm-for-woocommerce");
        $this->destination = $this->get_option('destination');
        $this->supports = ['products'];

        $this->logged_in = $this->get_option('logged_in');

        if (empty($this->destination) && $this->logged_in) {
            $this->logged_in = false;
            $this->update_option('logged_in', false);
        }

        $this->enabled = empty($this->logged_in) ? false : $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->currency = $this->get_option('currency');
        $this->issuer = $this->get_option('issuer');
        $this->explorer = $this->get_option('explorer');
        $this->api = $this->get_option('api');
        $this->api_secret = $this->get_option('api_secret');
        $this->currencies = $this->get_option('currencies');
        $this->issuers = $this->get_option('issuers');

        $this->init_form_fields();
		$this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ]);
        add_action( 'woocommerce_api_'. $this->id, array( $this, 'callback_handler' ));
        add_action( 'woocommerce_xumm_deactivate', [$this, 'deactivate']);
    }

    public function deactivate()
    {
        delete_option('woocommerce_xumm_settings');
    }

    public function init_form_fields()
    {
        apply_filters('xumm_init_form_fields', $this);
    }

    public function admin_options()
    {
        apply_filters('xumm_display_plugin_options', $this);
    }

    public function process_payment($order_id)
    {
        $paymentRequest = new PaymentRequest();
        $paymentRequest->setXummPaymentGateway($this);

        try
        {
            $result = $paymentRequest->processPayment($order_id);

            if (empty($result))
            {
                throw new \Exception(__('Got an error from XUMM', 'xumm-for-woocommerce'));
            }

            return [
                'result' => 'success',
                'redirect' => $result
            ];
        } catch (\Exception $e)
        {
            \wc_add_notice($e->getMessage(), 'error');
            return;
        }
    }

    public function callback_handler()
    {
        if (!empty($_GET["order_id"]))
        {
            $custom_identifier = sanitize_text_field($_GET["order_id"]);
            $order_id = explode("_", $custom_identifier)[0];
            $order = wc_get_order( $order_id );

            $order_status  = $order->get_status();

            switch ($order_status)
            {
                case 'processing':
                    wc_add_notice(__('Order Status: Processing', 'xumm-for-woocommerce'));
                    $redirect_url = $this->get_return_url( $order );
                    break;
                case 'pending':
                    wc_add_notice(__('Order Status: Pending', 'xumm-for-woocommerce'));
                    $redirect_url = URL::getReturnURL($custom_identifier, $order, $this);
                    break;
                case 'on-hold':
                    wc_add_notice('Order status: On-hold', 'xumm-for-woocommerce');
                    $redirect_url = $order->get_checkout_payment_url(false);
                    break;
                case 'completed':
                    wc_add_notice('Order status: Completed', 'xumm-for-woocommerce');
                    $redirect_url = $this->get_return_url( $order );
                    break;
                case 'cancelled':
                    wc_add_notice(__('Your order has been cancelled, please try again.', 'xumm-for-woocommerce'), 'error' );
                    $redirect_url = $order->get_checkout_payment_url(false);
                    break;
                case 'failed':
                    wc_add_notice(__('Failed payment. Please try again!', 'xumm-for-woocommerce'), 'error' );
                    $redirect_url = $order->get_checkout_payment_url(false);
                    break;
                case 'refunded':
                    wc_add_notice(__('Order has been refunded', 'xumm-for-woocommerce'), 'notice' );
                    $redirect_url = $this->get_return_url( $order );
                    break;
                default:
                    wc_add_notice(__('There is something wrong with the order, please contact us.', 'xumm-for-woocommerce'), 'error' );
                    $redirect_url = $order->get_checkout_payment_url(false);
                    break;
            }

            wp_safe_redirect($redirect_url);

            exit;
        }
    }
}
