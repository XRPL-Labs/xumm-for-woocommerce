<?php
    $headers = array(
        'Content-Type' => 'application/json',
        'X-API-Key' => $this->api,
        'X-API-Secret' => $this->api_secret
    );

    $explorer = $this->explorer;

    $json = file_get_contents('php://input');
    $json = json_decode($json, true);

    $uuid = $json['payloadResponse']['payload_uuidv4'];

    if($uuid != null) {
        $custom_identifier = $json['custom_meta']['identifier'];
        if ($custom_identifier != null) {
            $data = getPayloadXummById($custom_identifier, $headers);
            if (!empty($data['payload'])) {

                switch ($data['payload']['tx_type']) {
                    
                    case 'Payment':
                        $txid = $data['response']['txid'];
                        $xr = $data['custom_meta']['blob']['xr'];

                        $txbody = getTransactionDetails($txid, $headers);

                        $order_id = explode("_", $custom_identifier)[0];
                        $order = wc_get_order( $order_id );
                        $delivered_amount = $txbody['transaction']['meta']['delivered_amount'];
                        if(!checkDeliveredAmount($delivered_amount, $order, $xr, $this->issuers, $txid, $explorer)) {
                            exit();
                        }

                        $order->payment_complete();
                        wc_reduce_stock_levels( $order_id );
                        
                        $success = $lang->callback->note->success;
                        // A notes to the customer (replace true with false to make it private)
                        $order->add_order_note( $success->thanks . '<br>'. $success->check .'<a href="'.$explorer.$txid.'"> '.$success->href.'</a>', true );
                
                        WC()->cart->empty_cart();
                        break;

                    case 'SignIn':
                        $account = $data['response']['account'];
                        if(!empty($account))
                            echo($account);
                            $this->update_option('destination', $account );
                        break;

                    case 'TrustSet':
                        
                        break;
                }
            }

        }
    }
?>