<?php

    $order_meta = get_post_meta($order_id, '_wc_shipment_tracking_items', true);
    $transaction_id = get_post_meta($order_id, '_transaction_id', true);
    $gateway = get_post_meta($order_id, '_payment_method', true);

    $order_meta = $order_meta[0]; // we take only the first tracking since Paypal doesn't accept more

    $post_data = array (
        'tracking_info' => $order_meta['tracking_number'],
        'order_id' => $order_id,
        'carrier_name' => $order_meta['tracking_provider'],
        'tracking_link' => $order_meta['tracking_id'],
        'transaction_id' => $transaction_id,
        'gateway' => $gateway
    );

    $result = teq_stf_curl_json_post(TEQ_STF_SERVER . '/orders', json_encode($post_data));

?>