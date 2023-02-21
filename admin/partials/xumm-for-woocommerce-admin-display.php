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

$sdk = new XummSdk($context->api, $context->api_secret);

if(!empty($_GET['xumm-id'])) {
    $xumm_id = sanitize_text_field($_GET['xumm-id']);

    $payload = $sdk->getPayloadByCustomId($xumm_id);

    //Todo:: first check if success
    if (!empty($payload->payload)) {
        switch ($payload->payload->txType)
        {
            case 'SignIn':
                $account = $payload->response->account;

                if(!empty($account))
                {
                    $context->update_option('destination', $account );
                    $context->update_option('logged_in', true );
                    $context->logged_in = true;

                    echo('<div class="notice notice-success"><p>'.__('Sign In successfull please check address & test payment', 'xumm-for-woocommerce').'</p></div>');
                }

                break;

            case 'TrustSet':
                if (!empty($payload->payload->request['LimitAmount']['issuer'])) {
                    $context->update_option('issuer', $payload->payload->request['LimitAmount']['issuer']);
                    echo('<div class="notice notice-success"><p>'.__('Trust Line Set successfull please check address & test payment', 'xumm-for-woocommerce').'</p></div>');
                }

                break;

            default:
                break;
        }
    }
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

            <button type="button" class="customFormActionBtn" id="set_destination" style="border-style: none; cursor:pointer; background-color: transparent;">
                <img src="<?php echo plugin_dir_url( __FILE__ ) .'/../../public/images/signin.svg'; ?>" width="220" style="padding:0" />
            </button>

            <table class="form-table">

                <?php

                    if (!empty($context->logged_in))
                    {
                        $context->generate_settings_html();
                    }

                    $storeCurrency = get_woocommerce_currency();

                    if(empty($context->api) || empty($context->api_secret)) echo('<div class="notice notice-info"><p>'. __('Please add XUMM API keys from', 'xumm-for-woocommerce') .' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a></p></div>');
                    else {

                        try {
                            $pong = $sdk->ping();

                            if(!empty($pong->call->uuidV4)) {
                                echo('<div class="notice notice-success"><p>'.__('Connected to the', 'xumm-for-woocommerce', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a></p></div>');

                                $webhookApi = $pong->application->webhookUrl;
                                $webhook = get_home_url() . '/?wc-api='. $context->id;
                                if($webhook != $webhookApi) echo('<div class="notice notice-error"><p>'.__('WebHook incorrect on', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a>, '.__('should be', 'xumm-for-woocommerce').' '.$webhook.'</p></div>');
                            }
                            else echo('<div class="notice notice-error"><p>'.__('Connection API Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a>. '. __('Check your API keys.', 'xumm-for-woocommerce') .'Error Code:'. $body['error']['code'].'</p></div>');

                        } catch (\Exception $e) {
                            echo ('<div class="notice notice-error"><p>'.__('Connection Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a></p></div>');
                        }

                    }

                    // exit;
                    if (!in_array($storeCurrency, $context->availableCurrencies)) echo('<div class="notice notice-error"><p>'.__('Please change store currency', 'xumm-for-woocommerce').'</p></div>');
                    if ($storeCurrency != 'XRP' && $context->currencies != 'XRP' && $storeCurrency != $context->currencies) echo('<div class="notice notice-error"><p>'.__('Please change store currency', 'xumm-for-woocommerce').'</p></div>');
                    if ($context->currencies != 'XRP' && empty($context->issuers) && get_woocommerce_currency() != 'XRP') echo('<div class="notice notice-error"><p>'.__('Please set the issuer and save changes', 'xumm-for-woocommerce').'</p></div>');

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
                        if (jQuery(this).find("input#specialAction").val() !== '') {
                            e.preventDefault()
                            jQuery.ajax({
                                url: document.location.href,
                                type: 'POST',
                                data: jQuery(this).serialize(),
                                success: function (response) {
                                    let tlResponse = jQuery(response).find("#customFormActionResult").html().trim()
                                    window.location.href = tlResponse
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
        <?php
