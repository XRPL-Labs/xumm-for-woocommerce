<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://xumm.app/
 * @since      1.0.0
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/admin/partials
 */

use Xrpl\XummSdkPhp\Payload\CustomMeta;
use Xrpl\XummSdkPhp\Payload\Options;
use Xrpl\XummSdkPhp\Payload\Payload;
use Xrpl\XummSdkPhp\Payload\ReturnUrl;
use Xrpl\XummSdkPhp\XummSdk;

if ($context->logged_in && !empty($context->api) && !empty($context->api_secret))
{
    $sdk = new XummSdk($context->api, $context->api_secret);
}

if(!empty($_POST["specialAction"])) {
    ?>
        <div id="customFormActionResult" style="display: none;">
            <?php
                $query_arr = array(
                    'page'      => 'wc-settings',
                    'tab'       => 'checkout',
                    'section'   => 'xumm',
                    'callback'  => true
                );

                $return_url = get_home_url() .'/wp-admin/admin.php?' . http_build_query($query_arr);

                switch($_POST["specialAction"]) {
                    case 'set_destination':
                        $identifier = 'sign-in_' . strtoupper(substr(md5(microtime()), 0, 10));

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
                        $identifier = 'trustline_' . strtoupper(substr(md5(microtime()), 0, 10));
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

                $response = $sdk->createPayload($payload);

                $redirect = $response->next->always;
                if (!empty($redirect)) {
                    // Redirect to the XUMM processor page
                    echo($redirect);
                } else {
                    echo('<div class="notice notice-error"><p>'.__('Connection API Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a>. '. __('Check your API keys.', 'xumm-for-woocommerce') .'Error Code:'. $body['error']['code'] .'</p></div>');
                }

            ?>
        </div>
    <?php
}
?>
<h2><?php _e('XUMM Payment Gateway for WooCommerce','xumm-for-woocommerce'); ?></h2>

<?php if (empty($context->logged_in)): ?>
<p><?php _e('Welcome to the XUMM Payment Gateway for Woocommerce', 'xumm-for-woocommerce') ?></p>

<p><?php _e('In order to continue, you need to complete these steps:', 'xumm-for-woocommerce') ?></p>
<ol>
    <li><?php _e('Go to the', 'xumm-for-woocommerce') ?> <a target="_blank" href="https://apps.xumm.dev/"><?php _e('Xumm Developer Console', 'xumm-for-woocommerce') ?></a></li>
    <li><?php _e('Create your app to get your API Credentials', 'xumm-for-woocommerce') ?></li>
    <li><?php _e('Define your WebHook URL like this: ', 'xumm-for-woocommerce') ?> <a href="<?php echo site_url() ?>?wc-api=xumm"><?php echo site_url() ?>?wc-api=xumm</a></li>
    <li><?php _e('Back here and signin using your Xumm App', 'xumm-for-woocommerce') ?></li>
    <li><?php _e('Place your API Credentials', 'xumm-for-woocommerce') ?></li>
    <li><?php _e('Set your Trustlines and go ahead!', 'xumm-for-woocommerce') ?></li>
</ol>

<?php endif ?>

<button type="button" class="customFormActionBtn" id="set_destination" style="border-style: none; cursor:pointer; background-color: transparent;">
    <img src="<?php echo plugin_dir_url( __FILE__ ) .'/../../public/images/signin.svg'; ?>" width="220" style="padding:0" />
</button>

<table class="form-table">

    <?php

        $storeCurrency = get_woocommerce_currency();

        if (!empty($context->logged_in))
        {
            $context->generate_settings_html();

            if(empty($context->api) || empty($context->api_secret)) echo('<div class="notice notice-info"><p>'. __('Please add XUMM API keys from', 'xumm-for-woocommerce') .' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a></p></div>');
            else {

                try {
                    $pong = $sdk->ping();

                    if(!empty($pong->call->uuidV4)) {
                        echo('<div class="notice notice-success is-dismissible"><p>'.__('Connected to the', 'xumm-for-woocommerce', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a></p></div>');

                        $webhookApi = $pong->application->webhookUrl;
                        $webhook = get_home_url() . '/?wc-api='. $context->id;
                        if($webhook != $webhookApi) echo('<div class="notice notice-error is-dismissible"><p>'.__('WebHook incorrect on', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a>, '.__('should be', 'xumm-for-woocommerce').' '.$webhook.'</p></div>');
                    }
                    else echo('<div class="notice notice-error is-dismissible"><p>'.__('Connection API Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a>. '. __('Check your API keys.', 'xumm-for-woocommerce') .'Error Code:'. $body['error']['code'].'</p></div>');

                } catch (\Exception $e) {
                    echo ('<div class="notice notice-error is-dismissible"><p>'.__('Connection Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a></p></div>');
                }

            }

            if (!in_array($storeCurrency, $context->availableCurrencies)) echo('<div class="notice notice-error"><p>'.__('Please change store currency', 'xumm-for-woocommerce').'</p></div>');
            if ($storeCurrency != 'XRP' && $context->currencies != 'XRP' && $storeCurrency != $context->currencies) echo('<div class="notice notice-error"><p>'.__('Please change store currency', 'xumm-for-woocommerce').'</p></div>');
            if ($context->currencies != 'XRP' && empty($context->issuers) && get_woocommerce_currency() != 'XRP') echo('<div class="notice notice-error"><p>'.__('Please set the issuer and save changes', 'xumm-for-woocommerce').'</p></div>');
        }

    ?>
</table>

<input type="hidden" id="specialAction" name="specialAction" value="">

<?php if (!empty($context->logged_in)): ?>
<button type="button" class="customFormActionBtn button-primary" id="set_trustline" disabled="disabled">
    <?php echo __('Add Trustline', 'xumm-for-woocommerce'); ?>
</button>
<?php endif; ?>

<script>
    jQuery(function () {
        jQuery("#mainform").submit(function (e) {
            xrpl_loader(true);
            if (jQuery(this).find("input#specialAction").val() !== '') {
                e.preventDefault()
                jQuery.ajax({
                    url: document.location.href,
                    type: 'POST',
                    data: jQuery(this).serialize(),
                    success: function (response) {
                        let tlResponse = jQuery(response).find("#customFormActionResult").html().trim()
                        xrpl_loader(false);
                        window.location.href = tlResponse;
                    }
                });
                return false
            }
        })
        jQuery(".customFormActionBtn").click(function () {

            jQuery("#specialAction").val(jQuery(this).attr('id'))
            jQuery("#mainform").trigger('submit')
        })
    });
</script>

<?php include_once(dirname(__FILE__) . '/xumm-for-woocommerce-admin-loader.php'); ?>
