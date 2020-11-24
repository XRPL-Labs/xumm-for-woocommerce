<?php
    function getPayloadXummById($id, $headers) {
        global $lang;
        $error = $lang->callback->error->xumm_payload;

        $response = wp_remote_get('https://xumm.app/api/v1/platform/payload/ci/'. $id, array(
            'method'    => 'GET',
            'headers'   => $headers
        ));

        if( is_wp_error( $response ) ) {
            wc_add_notice($error, 'error' );
            exit();
        }

        $body = json_decode( $response['body'], true );
        return $body;
    }

    function getTransactionDetails($txid, $headers) {
        global $lang;
        $error = $lang->callback->error->xumm_payload;

        if (empty($txid)) return false;
        $tx = wp_remote_get('https://xumm.app/api/v1/platform/xrpl-tx/'. $txid, array(
            'method'    => 'GET',
            'headers'   => $headers
        ));
        if( is_wp_error( $tx ) ) {
            $tx = wp_remote_get('https://data.ripple.com/v2/transactions/'. $txid, array(
                'method'    => 'GET',
                'headers'   => array(
                    'Content-Type' => 'application/json'
                )
            ));
            if(is_wp_error( $tx )) {
                wc_add_notice($error, 'error' );
                exit();
            }
        }
        return json_decode( $tx['body'], true );
    }

    function checkDeliveredAmount($delivered_amount, $order, $xr, $issuers, $txid, $explorer) {
        global $lang;
        $error = $lang->callback->error;
        $note = $lang->callback->note;

        $total = $order->get_total();
        if($delivered_amount != null) {
            switch (gettype($delivered_amount)) {
                case 'string':
                    if(!is_numeric($delivered_amount)) {
                        wc_add_notice($error->amount, 'error');
                        return false;
                    break;
                    }
                case 'integer':
                    $delivered_amount = $delivered_amount/1000000;
                    $total = $total * $xr;
                    if($delivered_amount < ($total-0.000001)) {
                        if($delivered_amount == 0) wc_add_notice($error->zero, 'error');
                        else {
                            wc_add_notice($error->insufficient, 'error' );
                            $order->add_order_note($note->insufficient->message .'<br>'.$note->insufficient->paid .' XRP '. number_format($delivered_amount, 6) .'<br>'. $note->insufficient->open .' XRP '. number_format(($total - $delivered_amount), 6) .'<br>'. '<a href="'.$explorer.$txid.'">'. $note->insufficient->information .'</a>',true);
                        }
                        return false;
                    } else return true;
                break;

                case 'array':
                    if($delivered_amount['issuer'] != $issuers) {
                        wc_add_notice( $error->issuer, 'error' );
                        $order->add_order_note($note->issuer->message .'<br>'.$note->issuer->paid .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. $note->issuer->information .'</a>',true);
                        return false;
                    }

                    if($delivered_amount['currency'] != $order->get_currency()) {
                        wc_add_notice( $error->currency, 'error' );
                        $order->add_order_note($note->currency->message .'<br>'.$note->currency->paid .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br> <a href="'.$explorer.$txid.'">'. $note->currency->information .'</a>',true);
                        return false;
                    }

                    if($delivered_amount['value'] <= ($total * 0.99)) {
                        if($delivered_amount['value'] == 0) wc_add_notice($error->zero, 'error');
                        else {
                            wc_add_notice($error->insufficient, 'error');
                            $order->add_order_note($note->insufficient->message .'<br>'.$note->insufficient->paid .' '. $delivered_amount['currency'] .' '. $delivered_amount['value'] .'<br>'. $note->insufficient->open .' '. $delivered_amount['currency'] .' '. ($total-$delivered_amount['value']) .'<br>'. '<a href="'.$explorer.$txid.'">'. $note->insufficient->information .'</a>',true);
                        }
                        return false;
                    }

                    else return true;
                break;

                default:
                    wc_add_notice($error->amount, 'error');
                    return false;
                break; 
            }
        } else {
            wc_add_notice($error->amount, 'error');
            return false;
        }
    }

    function getReturnUrl($custom_identifier, $order, $self) {
        global $lang;
        $error = $lang->callback->error;
        $success = $lang->callback->note->success;

        $explorer = $self->explorer;

        $headers = array(
            'Content-Type' => 'application/json',
            'X-API-Key' => $self->api,
            'X-API-Secret' => $self->api_secret
        );

        $payload = getPayloadXummById($custom_identifier, $headers);
        $txid = $payload['response']['txid'];
        $xr = $payload['custom_meta']['blob']['xr'];

        $txbody = getTransactionDetails($txid, $headers);
        if(empty($txbody)) {
            wc_add_notice($error->payment, 'error' );
            return $order->get_checkout_payment_url(false);
        }
        $delivered_amount = $txbody['transaction']['meta']['delivered_amount'];
        if(!checkDeliveredAmount($delivered_amount, $order, $xr, $self->issuers, $txid, $explorer)) {
            $redirect_url = $order->get_checkout_payment_url(false);
            return $redirect_url;
        } else {
            $order->payment_complete();
            wc_reduce_stock_levels( $order->get_id() );
            $order->add_order_note( $success->thanks . '<br>'. $success->check .' <a href="'.$explorer.$txid.'">'.$success->href.'</a>', true );
            WC()->cart->empty_cart();
            return WC_Payment_Gateway::get_return_url( $order );
        }
    }
?>