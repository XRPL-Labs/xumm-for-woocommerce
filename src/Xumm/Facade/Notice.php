<?php

namespace Xrpl\XummForWoocommerce\Xumm\Facade;

class Notice
{
    /**
     * Add a flash notice to {prefix}options table until a full page refresh is done
     *
     * @param string $notice our notice message
     * @param string $type This can be "info", "warning", "error" or "success", "warning" as default
     * @param boolean $dismissible set this to TRUE to add is-dismissible functionality to your notice
     * @return void
     */
    public static function add_flash_notice($notice = "", $type = "success", $dismissible = true )
    {
        $notices = \get_transient( "woocommerce_xumm_admin_notices");
        if (empty($notices)) {
            $notices = [];
        }

        $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";

        $notices[] = [
            "notice" => $notice,
            "type" => $type,
            "dismissible" => $dismissible_text
        ];

        \set_transient("woocommerce_xumm_admin_notices", $notices );
    }
}
