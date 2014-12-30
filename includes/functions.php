<?php

/**
 * Get the country code from the IP address
 *
 * @param string  	$ip 			IP address, defaults to user if not present.
 * @global Array 	$edd_options   	Array of all the EDD Options
 * @return array 	$bodyarray 		Returned array from Taxamo API.
 */
function taxedd_get_country_code( $ip = "" ) {
	global $edd_options;

	if (isset( $edd_options['taxedd_private_token'] )) {

		$private_key = $edd_options['taxedd_private_token'];

		if ( !$ip ) {
			if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip=$_SERVER['HTTP_CLIENT_IP'];
			} elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip=$_SERVER['REMOTE_ADDR'];
			}
		}
		$taxresponse = wp_remote_get( 'https://api.taxamo.com/api/v1/geoip/'.$ip.'?private_token='. $private_key );

		if( ! is_wp_error( $taxresponse )
			&& isset( $taxresponse['response']['code'] )        
			&& 200 === $taxresponse['response']['code'] )
		{
			$body = wp_remote_retrieve_body( $taxresponse );
			$bodyarray = json_decode( $body ); 
			return $bodyarray;
		}
	}
}




/**
 * Get the VAT Details
 * @param  string $vatnumber the VAT Number to begin with.
 * @return array 	$bodyarray 		Returned array from Taxamo API.
 */
function taxedd_get_vat_details($vatnumber) {
	
	global $edd_options; 

	if (isset( $edd_options['taxedd_private_token'] )) {

		$private_key = $edd_options['taxedd_private_token'];
		$taxresponse = wp_remote_get( 'https://api.taxamo.com/api/v1/tax/vat_numbers/'.$vatnumber.'/validate?private_token=' . $private_key );

		if( ! is_wp_error( $taxresponse )
			&& isset( $taxresponse['response']['code'] )        
			&& 200 === $taxresponse['response']['code'] )
		{
			$body = wp_remote_retrieve_body( $taxresponse );
			$bodyarray = json_decode( $body, true ); 
			return $bodyarray;
		}
	}

	$bodyarray = array('buyer_tax_number_valid' => false);

	return $bodyarray;
}
?>
