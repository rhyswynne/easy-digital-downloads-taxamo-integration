<?php

/**
 * Adds to the checkout field the hidden value the taxamo software connects to, as well as
 * adds something to the privacy policy.
 *
 * @return void
 */
function taxedd_edd_country_code() {

	global $edd_options;
	global $user_ID;

	if ( is_user_logged_in() )
		$user_data = get_userdata( $user_ID );


	$intro_text = $edd_options['taxedd_introduction_text'];

	//echo '<p>' . $intro_text . '</p>';

	$taxamo = taxedd_get_country_code();

	if ( $taxamo && isset( $taxamo->country_code ) ) {
		?>
		<input class="edd-country" type="hidden" name="edd_country" id="edd-country" value="<?php echo $taxamo->country_code; ?>"/>
		<?php
	}
}
add_action( 'edd_purchase_form_user_info', 'taxedd_edd_country_code' );

/**
 * Stores the country code in the payment meta.
 *
 * @return void
 */
function taxamo_store_country_code( $payment_meta ) {
	$payment_meta['country']   = isset( $_POST['edd_country'] ) ? sanitize_text_field( $_POST['edd_country'] ) : '';
	return $payment_meta;
}
add_filter( 'edd_payment_meta', 'taxamo_store_country_code' );
