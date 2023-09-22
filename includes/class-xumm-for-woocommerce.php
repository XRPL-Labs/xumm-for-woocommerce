<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://xumm.app/
 * @since      1.0.0
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/includes
 */

use Xrpl\XummForWoocommerce\Xumm\Traits\XummPaymentGatewayTrait;
use Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/includes
 * @author     Andrei R <mdxico@gmail.com>
 */
class Xumm_For_Woocommerce {

    use XummPaymentGatewayTrait;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Xumm_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'XUMM_FOR_WOOCOMMERCE_VERSION' ) ) {
            $this->version = XUMM_FOR_WOOCOMMERCE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'xumm-for-woocommerce';

        $this->load_dependencies();
        $this->set_locale();

        if (is_admin()) {
            $this->define_admin_hooks();
        }

        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Xumm_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
     * - Xumm_For_Woocommerce_i18n. Defines internationalization functionality.
     * - Xumm_For_Woocommerce_Admin. Defines all hooks for the admin area.
     * - Xumm_For_Woocommerce_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xumm-for-woocommerce-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-xumm-for-woocommerce-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-xumm-for-woocommerce-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-xumm-for-woocommerce-public.php';

        $this->loader = new Xumm_For_Woocommerce_Loader();

        if (class_exists('WooCommerce'))
        {
            $this->setXummPaymentGateway(XummPaymentGateway::get_instance());
        }
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Xumm_For_Woocommerce_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Xumm_For_Woocommerce_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Xumm_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );


	$this->loader->add_action('plugins_loaded', $plugin_admin, 'load_plugin_admin');

        $this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices', 12, 1);
        $this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'show_indicator_toolbar', 500, 1);

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_woocommerce_menu_link', 55, 1);

        $this->loader->add_filter( 'plugin_action_links_xumm-payments-for-woocommerce/xumm-payments-for-woocommerce.php', $plugin_admin, 'settings_link', 10, 1);

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_filter('xumm_init_form_fields', $plugin_admin, 'init_form_fields', 1, 1);

        $this->loader->add_filter('xumm_display_plugin_options', $plugin_admin, 'display_plugin_options', 10, 2);

        $this->loader->add_action( 'wp_ajax_create_payload', $plugin_admin, 'ajax_create_payload', 12, 1);
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Xumm_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        if (class_exists('WooCommerce'))
        {
            $this->loader->add_filter('woocommerce_payment_gateways', $this, 'add_xumm_gateway_class');
            $this->loader->add_filter('woocommerce_available_payment_gateways', $this, 'disable_xumm');
            $this->loader->add_filter('woocommerce_currencies', $this, 'add_xrp_currency');
            $this->loader->add_filter('woocommerce_currency_symbol', $this, 'add_xrp_currency_symbol', 10, 2);
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Xumm_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Add XummPaymentGateway class as a method of Payment
     *
     * @since     0.5.1
     * @param array $methods
     * @return      array     List of available methods
     */
    public function add_xumm_gateway_class($methods)
    {
        $methods[] = 'Xrpl\XummForWoocommerce\Woocommerce\XummPaymentGateway';
        return $methods;
    }

    /**
     * Disable XUMM
     *
     * @since     0.5.1
     * @param array $available_gateways
     * @return    array       List of available gateways
     */
    public function disable_xumm($available_gateways)
    {
        $context = $this->getXummPaymentGateway();
        $storeCurrency = get_woocommerce_currency();

        if (empty($context->api) || empty($context->api_secret)) unset($available_gateways['xumm']);
        if (!in_array($storeCurrency, $context->availableCurrencies)) unset($available_gateways['xumm']);
        if ($storeCurrency != 'XRP' && $context->currencies != 'XRP' && $storeCurrency != $context->currencies) unset($available_gateways['xumm']);
        if ($context->currencies != 'XRP' && empty($context->issuers)) unset($available_gateways['xumm']);

        return $available_gateways;
    }

    /**
     * ADD XRP as a currency
     *
     * @since     0.5.1
     * @param array $xrp_currency
     * @return      array       List of currencies
     */
    public function add_xrp_currency( $xrp_currency ) {
        $xrp_currency['XRP'] = __( 'XRP', 'woocommerce' );
        $xrp_currency['ETH'] = __( 'Ethereum', 'woocommerce' );
        return $xrp_currency;
    }

    /**
     * ADD XRP currency symbol
     *
     * @since     0.5.1
     * @param string $custom_currency_symbol
     * @param string $custom_currency
     *
     * @return      string       Custom currency symbol
     */
    public function add_xrp_currency_symbol( $custom_currency_symbol, $custom_currency ) {
        switch( $custom_currency ) {
            case 'XRP': $custom_currency_symbol = 'XRP '; break;
            case 'ETH': $custom_currency_symbol = 'Ξ'; break;
        }
        return $custom_currency_symbol;
    }
}
