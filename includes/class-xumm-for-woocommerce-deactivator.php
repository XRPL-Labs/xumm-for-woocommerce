<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://xumm.app/
 * @since      1.0.0
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/includes
 * @author     Andrei R <mdxico@gmail.com>
 */
class Xumm_For_Woocommerce_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        apply_filters('woocommerce_xumm_deactivate', null);
    }

}
