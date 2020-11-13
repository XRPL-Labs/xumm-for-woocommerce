<?php
    require 'language.php';

            $admin = $lang->admin;

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
                $data = getXummData(sanitize_text_field($_GET['xumm-id']), $this);
                //Todo:: first check if success
                if (!empty($data['payload'])) {
                    switch ($data['payload']['tx_type']) {
                        case 'SignIn':
                            $account = $data['response']['account'];
                            if(!empty($account))
                                $this->update_option('destination', $account );
                                echo('<div class="notice notice-success"><p>'.$admin->signin->success.'</p></div>');
                            break;
    
                        case 'TrustSet':
                            //Todo show message when trustset is success with: $admin->trustset->success
                            break;
                        
                        default:
                            break;
                    }
                }
            }

            ?>
            <h2><?php _e($admin->title,'woocommerce'); ?></h2>
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
                                                "Account" => $this->destination,
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
                                        echo('<div class="notice notice-error"><p>'.$admin->api->redirect_error.' <a href="https://apps.xumm.dev/">'. $admin->api->href .'</a>. '. $admin->api->keys .'Error Code:'. $body['error']['code'] .'</p></div>');
                                   }
                            
                               } else {
                                    echo('<div class="notice notice-error"><p>'.$admin->api->no_response.' <a href="https://apps.xumm.dev/">'. $admin->api->href .'</a>.</p></div>');
                               }
                            ?>
                        </div>
                    <?php
                }
            ?>
            <table class="form-table">
                <?php
                $this->generate_settings_html();
                $storeCurrency = get_woocommerce_currency();
                    if(empty($this->api) || empty($this->api_secret)) echo('<div class="notice notice-info"><p>'. $admin->api->no_keys .' <a href="https://apps.xumm.dev/">'. $admin->api->href .'</a></p></div>');
                    else {
                        $response = wp_remote_get('https://xumm.app/api/v1/platform/ping', array(
                            'method'    => 'GET',
                            'headers'   => array(
                                'Content-Type' => 'application/json',
                                'X-API-Key' => $this->api,
                                'X-API-Secret' => $this->api_secret
                                )
                            ));
                        if( !is_wp_error( $response ) ) {
                            $body = json_decode( $response['body'], true );
                            if(!empty($body['pong'] && $body['pong'] == true)) {
                                echo('<div class="notice notice-success"><p>'.$admin->api->ping_success.' <a href="https://apps.xumm.dev/">'.$admin->api->href.'</a></p></div>');
                                
                                $webhookApi = $body['auth']['application']['webhookurl'];
                                $webhook = get_home_url() . '/?wc-api='. $this->id;
                                if($webhook != $webhookApi) echo('<div class="notice notice-error"><p>'.$admin->api->incorrect_webhook.' <a href="https://apps.xumm.dev/">'.$admin->api->href.'</a>, '.$admin->api->corrected_webhook.' '.$webhook.'</p></div>');
                            }
                            else echo('<div class="notice notice-error"><p>'.$admin->api->ping_error.' <a href="https://apps.xumm.dev/">'.$admin->api->href.'</a>. '.$admin->api->keys .'Error Code:'. $body['error']['code'].'</p></div>');
                        } else {
                            echo('<div class="notice notice-error"><p>'.$admin->api->no_response.' <a href="https://apps.xumm.dev/">'.$admin->api->href.'</a></p></div>');
                       }
                    }
                    if(!in_array($storeCurrency, $this->availableCurrencies)) echo('<div class="notice notice-error"><p>'.$admin->currency->store_unsupported.'</p></div>');
                    if ($storeCurrency != 'XRP' && $this->currencies != 'XRP' && $storeCurrency != $this->currencies) echo('<div class="notice notice-error"><p>'.$admin->currency->gateway_unsupported.'</p></div>');
                ?>
            </table>

            <input type="hidden" id="specialAction" name="specialAction" value="">
            <button type="button" class="customFormActionBtn" id="set_destination" style="border-style: none; cursor:pointer; background-color: Transparent;">
                <?php echo(file_get_contents(dirname(plugin_dir_path( __FILE__ )) .'/public/images/signin.svg')); ?>
            </button>
            <button type="button" class="customFormActionBtn button-primary" id="set_trustline">
                <?php echo ($admin->trustset->button); ?>
            </button>

            <script>
                jQuery("form#mainform").submit(function (e) {
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
                jQuery("button.customFormActionBtn").click(function () {
                    jQuery("input#specialAction").val(jQuery(this).attr('id'))
                    jQuery("form#mainform").submit()
                })
            </script>
        <?php
