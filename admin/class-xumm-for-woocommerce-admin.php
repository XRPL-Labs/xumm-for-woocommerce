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
class Xumm_For_Woocommerce_Admin {

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
        include_once( 'partials/xumm-for-woocommerce-admin-form-fields.php' );
    }

    /**
     * Display plugin options on woocommerce settings for XUMM Gateway
     *
     * @param object $context
     * @since    1.0.0
     */
    public function display_plugin_options($context) {
        include_once( 'partials/xumm-for-woocommerce-admin-display.php' );
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
}