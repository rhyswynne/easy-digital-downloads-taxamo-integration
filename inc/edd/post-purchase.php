<?php

/**
 *
 *
 * @param int     $payment_id ID of the EDD order just completed.
 * @global Array $edd_options   Array of all the EDD Options
 * @return void
 */
function taxedd_submit_order_to_taxamo( $payment_id ) {
    global $edd_options;

    if ( isset( $edd_options['taxedd_private_key'] ) ) {

        $private_key = $edd_options['taxedd_private_key'];

        $custom_id = $edd_options['taxedd_custom_id_format'];
        $custom_invoice  = $edd_options['taxedd_custom_invoice_format'];

        $custom_id = str_replace( '%%ID%%', $payment_id, $custom_id );
        $custom_invoice = str_replace( '%%ID%%', $payment_id, $custom_invoice );

        $taxamo = new Taxamo(new APIClient($private_key, 'https://api.taxamo.com'));

        // Basic payment meta
        $payment_meta = edd_get_payment_meta( $payment_id );
        
        // Cart details
        $cart_items = edd_get_payment_meta_cart_details( $payment_id );
        
        //wp_die( print_r( $payment_meta ) . ' ' . print_r($cart_items) );
        $transactionarray = array();

        foreach ( $payment_meta['cart_details'] as $cart_detail ) {
            $transaction_line = new Input_transaction_line();
            $transaction_line->amount = $cart_detail['price'];
            $transaction_line->custom_id = $cart_detail['name'];
            array_push( $transactionarray, $transaction_line );

        }

        $transaction = new Input_transaction();

        $transaction->currency_code = $payment_meta['currency'];
        $transaction->buyer_ip = $_SERVER['REMOTE_ADDR'];
        $transaction->billing_country_code = $payment_meta['country'];
        $transaction->buyer_email = $payment_meta['email'];
        $transaction->original_transaction_key = $payment_meta['key'];
        $transaction->custom_id = $custom_id;
        $transaction->invoice_number = $custom_invoice;

        if ( isset( $payment_meta['user_info']['first_name'] ) && isset( $payment_meta['user_info']['last_name'] ) ) {
            $transaction->buyer_name = $payment_meta['user_info']['first_name'] . " " . $payment_meta['user_info']['last_name'];
        }
        //$transaction->invoice_address = $payment_meta['address'];

        $transaction->transaction_lines = $transactionarray;

        //wp_die( print_r( $transaction ));

        $resp = $taxamo->createTransaction( array( 'transaction' => $transaction ) );

        $taxamo->confirmTransaction( $resp->transaction->key, array( 'transaction' => $transaction ) );
    }
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
