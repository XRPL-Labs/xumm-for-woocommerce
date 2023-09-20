<?php

namespace Xrpl\XummForWoocommerce\Woocommerce;

use Xrpl\XummForWoocommerce\Xumm\Facade\Notice;
use Xrpl\XummForWoocommerce\Xumm\Facade\URL;
use Xrpl\XummForWoocommerce\Xumm\Request\PaymentRequest;

class XummPaymentGateway extends \WC_Payment_Gateway
{
    /**
     * @var self|null $instance
     */
    protected static self|null $instance = null;

    /**
     * @var array<string, string> $availableCurrencies
     */
    public array $availableCurrencies = [
        'XRP' => 'XRP',
        'USD' => 'USD',
        'EUR' => 'EUR'
    ];

    /**
     * @var string $currencies
     */
    public string $currencies;

    /**
     * @var string $destination
     */
    public string $destination;

    /**
     * @var string $issuer
     */
    public string $issuer;

    /**
     * @var string $explorer
     */
    public string $explorer;

    /**
     * @var string $api
     */
    public string $api;

    /**
     * @var string $api_secret
     */
    public string $api_secret;

    /**
     * @var string $issuers
     */
    public string $issuers;

    /**
     * @var bool $logged_in
     */
    public bool $logged_in;

    /**
     * @var string $currency
     */
    public string $currency;

    /**
     * @var string $xrpl_network
     */
    public string $xrpl_network;

    public function __construct()
    {
        $this->id = 'xumm';
        $this->icon = \xumm_plugin_url() . 'admin/public/images/label.svg';
        $this->has_fields = false;
        $this->method_title = __("Accept XUMM payments", "xumm-for-woocommerce");
        $this->method_description = __("Receive any supported currency into your XRP account using XUMM", "xumm-for-woocommerce");
        $this->destination = $this->get_option('destination');
        $this->supports = ['products'];

        $this->logged_in = (bool) $this->get_option('logged_in', !empty($this->destination) ? true : false);

        if (empty($this->destination) && $this->logged_in)
        {
            $this->logged_in = false;
            $this->update_option('logged_in', false);
        }

        $this->enabled = empty($this->logged_in) ? 'false' : $this->get_option('enabled');
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->currency = $this->get_option('currency');
        $this->issuer = $this->get_option('issuer');
        $this->explorer = $this->get_option('explorer');
        $this->api = $this->get_option('api');
        $this->api_secret = $this->get_option('api_secret');
        $this->currencies = $this->get_option('currencies');
        $this->issuers = $this->get_option('issuers');
        $this->xrpl_network = $this->get_option('xrpl_network', 'mainnet');

        $this->init_form_fields();
		$this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_xumm', [ $this, 'process_admin_options' ]);

        add_action( 'woocommerce_api_xumm', [$this, 'callback_handler']);
        add_action( 'woocommerce_xumm_deactivate', [$this, 'deactivate']);
    }

    public function deactivate() : void
    {
        delete_option('woocommerce_xumm_settings');
    }

    public function init_form_fields() : void
    {
        apply_filters('xumm_init_form_fields', $this);
    }

    public function admin_options() : void
    {
        apply_filters('xumm_display_plugin_options', $this);
    }

    /**
     * @return array<string, string>
     */
    public function process_payment(mixed $order_id) : array
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
            return [];
        }
    }

    public function callback_handler() : void
    {
        global $wp_query;

        if (!empty($_GET["order_id"]))
        {
            $custom_identifier = sanitize_text_field($_GET["order_id"]);
            $order_id = explode("-", $custom_identifier)[0];
            $order = wc_get_order( $order_id );

            if (empty($order))
            {
                $wp_query->set_404();
                status_header( 404 );
                get_template_part('404');
                exit();
            }

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

    public static function get_instance() : self
    {
        if (self::$instance == null)
        {
            self::$instance = new XummPaymentGateway;
        }

        return self::$instance;
    }
}
