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

use XummForWoocomerce\XUMM\Facade\Notice;

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
        ' <a target="_blank" href="https://apps.xumm.dev/">' .
        __('Xumm Dev Console', 'xumm-for-woocommerce')  . '</a> ' .
        __('to get your API credentials', 'xumm-for-woocommerce') . '.';

        Notice::add_flash_notice($notice);
    }

}
