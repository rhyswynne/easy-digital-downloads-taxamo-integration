<?php


function taxedd_submit_order_to_taxamo($payment_id) {
	
	$taxamo = new Taxamo(new APIClient(TAXEDD_PRIVATE_KEY, 'https://api.taxamo.com')); 

	// Basic payment meta
    $payment_meta = edd_get_payment_meta( $payment_id );

	// Cart details
    $cart_items = edd_get_payment_meta_cart_details( $payment_id );

	//wp_die( print_r( $payment_meta ) . ' ' . print_r($cart_items) ); 
    $transactionarray = array();

    foreach ($payment_meta['cart_details'] as $cart_detail) {
        $transaction_line = new Input_transaction_line();
        $transaction_line->amount = $cart_detail['price'];
        $transaction_line->custom_id = $cart_detail['name'];
        array_push( $transactionarray, $transaction_line);

    }

    $transaction = new Input_transaction();
    $transaction->currency_code = $payment_meta['currency'];
    $transaction->buyer_ip = $_SERVER['REMOTE_ADDR'];
    $transaction->billing_country_code = $payment_meta['country'];
    $transaction->buyer_email = $payment_meta['email'];
    $transaction->original_transaction_key = $payment_meta['key'];
    if (isset( $payment_meta['user_info']['first_name'] ) && isset($payment_meta['user_info']['last_name'] )) {
        $transaction->buyer_name = $payment_meta['user_info']['first_name'] . " " . $payment_meta['user_info']['last_name'];
    }
	//$transaction->invoice_address = $payment_meta['address'];

    $transaction->transaction_lines = $transactionarray;

	//wp_die( print_r( $transaction ));

    $resp = $taxamo->createTransaction(array('transaction' => $transaction));

    $taxamo->confirmTransaction($resp->transaction->key, array('transaction' => $transaction)); 
}
add_action( 'edd_complete_purchase', 'taxedd_submit_order_to_taxamo' );

/*
Input_transaction Object
(
    [invoice_date] => 
    [invoice_address] => 
    [buyer_credit_card_prefix] => 
    [custom_fields] => 
    [additional_currencies] => 
    [buyer_tax_number] => 
    [custom_id] => 
    [tax_country_code] => 
    [force_country_code] => 
    [buyer_email] => 
    [original_transaction_key] => 
    [buyer_ip] => 127.0.0.1
    [invoice_place] => 
    [verification_token] => 
    [tax_deducted] => 
    [buyer_name] => 
    [evidence] => 
    [custom_data] => 
    [billing_country_code] => IE
    [invoice_number] => 
    [currency_code] => USD
    [description] => 
    [supply_date] => 
    [transaction_lines] => Array
        (
            [0] => Input_transaction_line Object
                (
                    [custom_fields] => 
                    [custom_id] => TEST Product
                    [product_type] => 
                    [quantity] => 
                    [unit_price] => 
                    [unit_of_measure] => 
                    [total_amount] => 
                    [tax_rate] => 
                    [line_key] => 
                    [amount] => 50
                    [informative] => 
                    [description] => 
                    [product_code] => 
                    [supply_date] => 
                    [tax_name] => 
                )

            [1] => Input_transaction_line Object
                (
                    [custom_fields] => 
                    [custom_id] => Item 2
                    [product_type] => 
                    [quantity] => 
                    [unit_price] => 
                    [unit_of_measure] => 
                    [total_amount] => 
                    [tax_rate] => 
                    [line_key] => 
                    [amount] => 100
                    [informative] => 
                    [description] => 
                    [product_code] => 
                    [supply_date] => 
                    [tax_name] => 
                )

            [2] => Input_transaction_line Object
                (
                    [custom_fields] => 
                    [custom_id] => Item 3
                    [product_type] => 
                    [quantity] => 
                    [unit_price] => 
                    [unit_of_measure] => 
                    [total_amount] => 
                    [tax_rate] => 
                    [line_key] => 
                    [amount] => 23
                    [informative] => 
                    [description] => 
                    [product_code] => 
                    [supply_date] => 
                    [tax_name] => 
                )

        )

    [order_date] => 
    ) */
?>