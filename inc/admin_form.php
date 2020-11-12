<?php
    require 'language.php';

            $form = $lang->form;

            $headers = array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->api,
                'X-API-Secret' => $this->api_secret
            );

            $response = wp_remote_get('https://xumm.app/api/v1/platform/curated-assets', array(
                'method'    => 'GET',
                'headers'   => $headers,
            ));
            $body = json_decode( $response['body'], true );

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => $form->enabled->title,
                    'label'       => $form->enabled->label,
                    'type'        => 'checkbox',
                    'description' => $form->enabled->description,
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => $form->title->title,
                    'type'        => 'text',
                    'description' => $form->title->description,
                    'default'     => $form->title->default,
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => $form->description->title,
                    'type'        => 'textarea',
                    'description' => $form->description->description,
                    'default'     => $form->description->default,
                ),
                'destination' => array(
                    'title'       => $form->destination->title,
                    'type'        => 'text',
                    'description' => $form->destination->description,
                    'desc_tip'    => true,
                ),
                'explorer' => array(
                    'title'       => $form->explorer->title,
                    'description' => $form->explorer->description,
                    'type'        => 'select',
                    'options'     => array(
                        'https://bithomp.com/explorer/' => 'Bithomp',
                        'https://xrpscan.com/tx/' => 'XRPScan',
                        'https://livenet.xrpl.org/transactions/' => 'XRPL.org'
                    ),
                    'default'     => 'https://bithomp.com/explorer/',
                    'desc_tip'    => true
                ),
                'api' => array(
                    'title'       => $form->api->title,
                    'type'        => 'text',
                    'description' => $form->api->description .' <a href="https://apps.xumm.dev/">https://apps.xumm.dev/</a>',
                    'default'     => '',
                ),
                'api_secret' => array(
                    'title'       => $form->api_secret->title,
                    'type'        => 'text',
                    'description' => $form->api_secret->description .' <a href="https://apps.xumm.dev/">https://apps.xumm.dev/</a>',
                    'default'     => '',
                )
            );

            $this->availableCurrencies['XRP'] = 'XRP';
            foreach ($body['currencies'] as $v) {
                if(get_woocommerce_currency() == $v){
                    $this->availableCurrencies[$v] = $v;
                }
            }

            $this->form_fields['currencies'] = array(
                'title'       => $form->currencies->title,
                'description' => $form->currencies->description,
                'type'        => 'select',
                'options'     => $this->availableCurrencies,
            );

            $availableIssuers = [];
            foreach ($body['details'] as $exchange) {
                $exchangeName = $exchange['name'];
                foreach ($exchange['currencies'] as $currency) {
                    $value = $currency['issuer'];
                    $availableIssuers[$value] = $exchangeName;
                }
            }

            $this->form_fields['issuers'] = array(
                'title'       => $form->issuers->title,
                'description' => $form->issuers->description,
                'type'        => 'select',
                'options'     => $availableIssuers,
                'default'     => ''
            );

             $body['account'] = $this->destination;
             $body['store_currency'] = get_woocommerce_currency();

            wp_localize_script( 'xumm_js', 'xumm_object', $body);

?>