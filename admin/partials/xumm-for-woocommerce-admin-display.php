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

use Xrpl\XummSdkPhp\XummSdk;

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php

            function getXummData($id, $self){
                $response = wp_remote_get('https://xumm.app/api/v1/platform/payload/ci/'. $id, array(
                    'method'    => 'GET',
                    'headers'   => array(
                        'Content-Type' => 'application/json',
                        'X-API-Key' => $self->api,
                        'X-API-Secret' => $self->api_secret
                    )
                ));
                $body = json_decode( $response['body'], true );
                return $body;
            }

            if(!empty($_GET['xumm-id'])) {
                $data = getXummData(sanitize_text_field($_GET['xumm-id']), $context);
                //Todo:: first check if success
                if (!empty($data['payload'])) {
                    switch ($data['payload']['tx_type']) {
                        case 'SignIn':
                            $account = $data['response']['account'];
                            if(!empty($account))
                                $context->update_option('destination', $account );
                                echo('<div class="notice notice-success"><p>'.$lang->admin->signin->success.'</p></div>');
                            break;

                        case 'TrustSet':
                            //Todo show message when trustset is success with: __('Trust Line Set successfull please check address & test payment', 'xumm-for-woocommerce')
                            break;

                        default:
                            break;
                    }
                }
            }

            ?>
            <h2><?php __('XUMM Payment Gateway for WooCommerce','woocommerce'); ?></h2>
            <?php
                if(!empty($_POST["specialAction"])) {
                    ?>
                        <div id="customFormActionResult" style="display: none;">
                            <?php
                                $query_arr = array(
                                    'page'      => 'wc-settings',
                                    'tab'       => 'checkout',
                                    'section'   => 'xumm'
                                );

                                $return_url = get_home_url() .'/wp-admin/admin.php?' . http_build_query($query_arr);

                                $headers = [
                                    'Content-Type' => 'application/json',
                                    'X-API-Key' => sanitize_text_field($_POST['woocommerce_xumm_api']),
                                    'X-API-Secret' => sanitize_text_field($_POST['woocommerce_xumm_api_secret'])
                                ];

                                switch($_POST["specialAction"]) {
                                    case 'set_destination':
                                        $identifier = 'sign-in_' . strtoupper(substr(md5(microtime()), 0, 10));
                                        $return_url = add_query_arg( 'xumm-id', $identifier, $return_url);
                                        $body = [
                                            "txjson" => [
                                                "TransactionType" => "SignIn"
                                            ],
                                            "options" => [
                                                "submit" => true,
                                                "return_url" => [
                                                    "web" => $return_url                                                ]
                                            ],
                                            'custom_meta' => array(
                                                'identifier' => $identifier
                                            )
                                        ];
                                        break;
                                    case 'set_trustline':
                                        $identifier = 'trustline_' . strtoupper(substr(md5(microtime()), 0, 10));
                                        $return_url = add_query_arg( 'xumm-id', $identifier, $return_url);
                                        $body = [
                                            "txjson" => [
                                                "TransactionType" => "TrustSet",
                                                "Account" => $context->destination,
                                                "Fee" => "12",
                                                "LimitAmount" => [
                                                  "currency" => sanitize_text_field($_POST['woocommerce_xumm_currencies']),
                                                  "issuer" => sanitize_text_field($_POST['woocommerce_xumm_issuers']),
                                                  "value" => "999999999"
                                                ]
                                            ],
                                            "options" => [
                                                "submit" => true,
                                                "return_url" => [
                                                    "web" => $return_url                                                ]
                                            ],
                                            'custom_meta' => array(
                                                'identifier' => $identifier
                                            )
                                        ];
                                        break;
                                }

                                if (wp_is_mobile()){
                                    $body['options']['return_url']['app'] = $return_url;
                                }

                                $body = wp_json_encode($body);

                                $response = wp_remote_post('https://xumm.app/api/v1/platform/payload', array(
                                    'method'    => 'POST',
                                    'headers'   => $headers,
                                    'body'      => $body
                                    )
                                );

                                if( !is_wp_error( $response ) ) {
                                    $body = json_decode( $response['body'], true );
                                    $redirect = $body['next']['always'];
                                    if ( $redirect != null ) {
                                       // Redirect to the XUMM processor page
                                        echo($redirect);
                                    } else {
                                        echo('<div class="notice notice-error"><p>'.$lang->admin->api->redirect__rror.' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a>. '. __('Check your API keys.', 'xumm-for-woocommerce') .'Error Code:'. $body['error']['code'] .'</p></div>');
                                   }

                               } else {
                                    echo('<div class="notice notice-error"><p>'.__('Connection Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a>.</p></div>');
                               }
                            ?>
                        </div>
                    <?php
                }
            ?>
            <table class="form-table">
                <?php

                    $context->generate_settings_html();

                    $storeCurrency = get_woocommerce_currency();

                    if(empty($context->api) || empty($context->api_secret)) echo('<div class="notice notice-info"><p>'. __('Please add XUMM API keys from', 'xumm-for-woocommerce') .' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a></p></div>');
                    else {

                        try {
                            $sdk = new XummSdk($context->api, $context->api_secret);
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
            <button type="button" class="customFormActionBtn" id="set_destination" style="border-style: none; cursor:pointer; background-color: transparent;">
                <img src="<?php echo plugin_dir_url( __FILE__ ) .'/../../public/images/signin.svg'; ?>" width="220" style="padding:0" />
            </button>

            <button type="button" class="customFormActionBtn button-primary" id="set_trustline" disabled="disabled">
                <?php echo __('Add Trustline', 'xumm-for-woocommerce'); ?>
            </button>

            <script>
                /* jQuery(function () {
                    jQuery("#mainform").submit(function (e) {
                        alert(document.location.href);
                        if (jQuery(this).find("input#specialAction").val() !== '') {
                            e.preventDefault()
                            alert('aqui');
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
                }); */
            </script>
        <?php
