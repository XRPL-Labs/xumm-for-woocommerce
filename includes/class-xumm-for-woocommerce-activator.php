<?php

/**
 * Fired during plugin activation
 *
 * @link       https://xumm.app/
 * @since      1.0.0
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/includes
 */

use Xrpl\XummForWoocommerce\Xumm\Facade\Notice;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/includes
 * @author     Andrei R <mdxico@gmail.com>
 */
class Xumm_For_Woocommerce_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        $notice = '&#127881; ' .
        __('Thank you for using XUMM For Woocommerce!', 'xumm-for-woocommerce') .
        ' ' .
        __('Now proceed to', 'xumm-for-woocommerce') .
        ' <a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=xumm' ) . '">' .
        __('settings', 'xumm-for-woocommerce')  . '</a> ' .
        __('to complete the plugin setup', 'xumm-for-woocommerce') . '.';

        Notice::add_flash_notice($notice);
    }

}
