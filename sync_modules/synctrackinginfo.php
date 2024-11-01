<?php

    $transaction_id = get_post_meta($order_id, '_transaction_id', true);
    $gateway = get_post_meta($order_id, '_payment_method', true);

    $post_data = array (
        'order_id' => $order_id,
        'transaction_id' => $transaction_id,
        'gateway' => $gateway
    );

    $result = teq_stf_curl_json_post(TEQ_STF_SERVER . '/orders/sync', json_encode($post_data));

?>
