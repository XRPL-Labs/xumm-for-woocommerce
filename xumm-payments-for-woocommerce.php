<?php

/**
 * Plugin Name: XUMM payments for WooCommerce
 * Plugin URI:  https://github.com/XRPL-Labs/xumm-for-woocommerce
 * Description: Make XRP payments using XUMM
 * Author:      XUMM
 * Author URI:  https://xumm.app/
 * Version:     1.0.2
 * License:     GPL v2 or later
 * License URI: https://xrpl-labs.com/static/documents/XRPL-Labs-Terms-of-Service-V1.pdf
 */

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://xumm.app/
 * @since             1.0.0
 * @package           Xumm_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       XUMM payments for WooCommerce
 * Plugin URI:        https://github.com/XRPL-Labs/xumm-for-woocommerce
 * Description:       Make XRP payments using XUMM
 * Version:           1.0.2
 * Author:            XUMM
 * Author URI:        https://xumm.app/
 * License:           GPL v2 or later
 * License URI:       https://xrpl-labs.com/static/documents/XRPL-Labs-Terms-of-Service-V1.pdf
 * Text Domain:       xumm-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'XUMM_FOR_WOOCOMMERCE_VERSION', '1.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xumm-for-woocommerce-activator.php
 */
function activate_xumm_for_woocommerce() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-xumm-for-woocommerce-activator.php';
    Xumm_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xumm-for-woocommerce-deactivator.php
 */
function deactivate_xumm_for_woocommerce() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-xumm-for-woocommerce-deactivator.php';
    Xumm_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_xumm_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_xumm_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

require plugin_dir_path( __FILE__ ) . 'libraries/autoload.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-xumm-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xumm_for_woocommerce() {
    $plugin = new Xumm_For_Woocommerce();
    $plugin->run();
}

run_xumm_for_woocommerce();

function xumm_plugin_url() {
    return plugin_dir_url( __FILE__ );
}

function xumm_plugin_path() {
    return plugin_dir_path( __FILE__ );
}
