<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://xumm.app/
 * @since      1.0.0
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/admin
 */

use Xrpl\XummSdkPhp\Payload\CustomMeta;
use Xrpl\XummSdkPhp\Payload\Options;
use Xrpl\XummSdkPhp\Payload\Payload;
use Xrpl\XummSdkPhp\Payload\ReturnUrl;
use Xrpl\XummSdkPhp\XummSdk;

use Xrpl\XummForWoocommerce\Xumm\Callback\SignInHandler;
use Xrpl\XummForWoocommerce\Xumm\Callback\TrustSetHandler;
use Xrpl\XummForWoocommerce\Xumm\Exception\SignInException;
use Xrpl\XummForWoocommerce\Xumm\Exception\TrustSetException;
use Xrpl\XummForWoocommerce\Xumm\Facade\Notice;
use Xrpl\XummForWoocommerce\Xumm\Traits\XummPaymentGatewayTrait;
use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/admin
 * @author     Andrei R <mdxico@gmail.com>
 */
class Xumm_For_Woocommerce_Admin
{
    use XummPaymentGatewayTrait;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Xumm_For_Woocommerce_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Xumm_For_Woocommerce_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if ($this->is_wc_settings_page())
        {
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/xumm-for-woocommerce-admin.css', array(), $this->version, 'all' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Xumm_For_Woocommerce_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Xumm_For_Woocommerce_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ($this->is_wc_settings_page()) {
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/xumm-for-woocommerce-admin.js', array( 'jquery' ), $this->version, false );
        }
    }

    /**
     * Initialize form fields for the admin area.
     *
     * @param object $context
     * @since    1.0.0
     */
    public function init_form_fields($context)
    {
        include('partials/xumm-for-woocommerce-admin-form-fields.php');
    }

    /**
     * Display plugin options on woocommerce settings for XUMM Gateway
     *
     * @param object $context
     * @since    1.0.0
     */
    public function display_plugin_options($context) {
        include_once('partials/xumm-for-woocommerce-admin-display.php');
    }

    /**
     * Check if current page is woocommerce settings page
     *
     * @since    1.0.0
     */
    public function is_wc_settings_page()
    {
        global $current_screen;

        if ($current_screen->id != 'woocommerce_page_wc-settings') {
            return false;
        }

        if (!empty($_GET['tab']) && $_GET['tab'] != 'checkout') {
            return false;
        }

        if (!empty($_GET['section']) && $_GET['section'] != 'xumm') {
            return false;
        }

        return true;
    }

    /**
     * Give the notifications on the admin area
     *
     * @since    1.0.0
     */
    public function admin_notices()
    {
        $notices = get_transient( "woocommerce_xumm_admin_notices");

        if (!empty($notices))
        {
            foreach ($notices as $notice)
            {
                printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                    $notice['type'],
                    $notice['dismissible'],
                    $notice['notice']
                );
            }

            delete_transient("woocommerce_xumm_admin_notices");
        }
    }

    /**
     * Settings link after activate plugin
     *
     * @since    1.0.0
     */
    public function settings_link($links)
    {
        $action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=xumm' ) . '" aria-label="' . esc_attr__( 'View Xumm For Woocommerce settings', 'xumm-for-woocommerce' ) . '">' . esc_html__( 'Settings', 'woocommerce' ) . '</a>',
		);

		return array_merge( $action_links, $links );
    }

    /**
     * Xumm callback fires after receive xumm-id parameter
     *
     * @since    1.0.0
     */
    public function xumm_callback()
    {
        $context = $this->getXummPaymentGateway();

        if(!empty($_GET['xumm-id']))
        {
            $xumm_id = sanitize_text_field($_GET['xumm-id']);

            $sdk = new XummSdk($context->api, $context->api_secret);
            $payload = $sdk->getPayloadByCustomId($xumm_id);

            if (!empty($payload->payload))
            {
                $type = '';
                $message = '';

                switch ($payload->payload->txType)
                {
                    case 'SignIn':

                        try
                        {
                            $handler = new SignInHandler($context, $payload);
                            $handler->handle();

                            $type = 'success';
                            $message = __('Sign In successfull please check address & test payment', 'xumm-for-woocommerce');

                        } catch (SignInException $e)
                        {
                            $type = 'error';
                            $message = $e->getMessage();
                        }

                        Notice::add_flash_notice($message, $type);

                        break;

                    case 'TrustSet':

                        try
                        {
                            $handler = new TrustSetHandler($context, $payload);
                            $handler->handle();

                            $type = 'success';
                            $message = __('Trust Line Set successfull please check address & test payment', 'xumm-for-woocommerce');

                        } catch (TrustSetException $e)
                        {
                            $type = 'error';
                            $message = $e->getMessage();
                        }

                        Notice::add_flash_notice($message, $type);

                        break;

                    default:
                        break;
                }
            }

            $location = admin_url('admin.php?page=wc-settings&tab=checkout&section=xumm');

            wp_redirect($location);

            exit;
        }
    }

    /**
     * Create SignIn and TrustSet payload via Ajax Request
     *
     * @since    1.0.0
     */
    public function ajax_create_payload()
    {
        $context = $this->getXummPaymentGateway();
        $sdk = new XummSdk($context->api, $context->api_secret);

        $query_arr = array(
            'page'      => 'wc-settings',
            'tab'       => 'checkout',
            'section'   => 'xumm',
            'callback'  => true
        );

        $return_url = get_home_url() .'/wp-admin/admin.php?' . http_build_query($query_arr);

        switch ($_POST["value"])
        {
            case 'set_destination':
                $identifier = strtoupper(uniqid('signin-'));

                $return_url = add_query_arg( 'xumm-id', $identifier, $return_url);

                $payload = new Payload(
                    [
                        "TransactionType" => "SignIn"
                    ],
                    null,
                    new Options(true, null, null, null, null,
                    new ReturnUrl(wp_is_mobile() ? $return_url : null, $return_url)),
                    new CustomMeta($identifier)
                );

                break;

            case 'set_trustline':
                $identifier = strtoupper(uniqid('trustline-'));
                $return_url = add_query_arg( 'xumm-id', $identifier, $return_url);

                $payload = new Payload(
                    [
                        'TransactionType' => 'TrustSet',
                        "Account" => $context->destination,
                        "Fee" => "12",
                        "LimitAmount" => [
                            "currency" => sanitize_text_field($_POST['woocommerce_xumm_currencies']),
                            "issuer" => sanitize_text_field($_POST['woocommerce_xumm_issuers']),
                            "value" => "999999999"
                        ],
                        "Flags" => 131072
                    ],
                    null,
                    new Options(true, null, null, null, null,
                        new ReturnUrl(wp_is_mobile() ? $return_url : null, $return_url)),
                    new CustomMeta($identifier)
                );

                break;
        }

        try
        {
            $response = $sdk->createPayload($payload);

            $redirect = $response->next->always;

            if (empty($redirect))
            {
                throw new \Exception(__('Connection API Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a>. '. __('Check your API keys.', 'xumm-for-woocommerce'));
            }

            return wp_send_json([
                'status' => 'ok',
                'message' => '',
                'redirect_url' => $redirect
            ], 200);

        } catch (\Exception $e)
        {
            return wp_send_json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'redirect_url' => ''
            ], 200);
        }

        wp_die();
    }

    /**
	 * Toolbar indicator of XRPL Network
	 * @param WP_Admin_Bar $admin_bar
	 */
	public function show_indicator_toolbar($admin_bar)
    {
        $context = $this->getXummPaymentGateway();

        if (!empty($context->api) && !empty($context->api_secret))
        {
            if (current_user_can('activate_plugins') && current_user_can('manage_options'))
            {
                $xrpl_network = $context->get_option('xrpl_network', 'mainnet');
                $network = $xrpl_network == 'mainnet' ? 'Main net' : 'Test net';
                $styles = $xrpl_network == 'testnet' ? 'opacity: .20' : '';

                $iconhtml = sprintf( '<span class="ab-icon"><img src="%s" style="height: 14px; %s" /></span> %s', xumm_plugin_url() . 'admin/public/images/xrp-symbol-white.svg', $styles, $network );

                $admin_bar->add_node([
                    'id'		=> 'xumm-for-woocommerce-indicator',
                    'title'     => $iconhtml,
                    'href'		=> admin_url('admin.php?page=wc-settings&tab=checkout&section=xumm'),
                    'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode($iconSVG),
                    'meta'		=> [
                        'title' => $network
                    ],
                ]);
            }
        }
	}

    /**
	 * Add submenu link to Woocommerce admin menu
	 */
    public function add_woocommerce_menu_link() {
        add_submenu_page(
            'woocommerce',
            __('XUMM', 'woocommerce-xumm'),
            __('XUMM', 'woocommerce-xumm'),
            'manage_woocommerce',
            admin_url('admin.php?page=wc-settings&tab=checkout&section=xumm'),
            null );
    }

    public function load_plugin_admin()
    {
        if (class_exists('WooCommerce')) {
            $this->setXummPaymentGateway(XummPaymentGateway::get_instance());
            add_filter('admin_init', [$this, 'xumm_callback'], 10, 1);
        }
    }
}
