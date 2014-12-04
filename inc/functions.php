<?php

/**
 * Get the country code from the IP address
 *
 * @param string  	$ip IP address, defaults to user if not present.
 * @return array 	$bodyarray Returned array from Taxamo API.
 */
function taxedd_get_country_code( $ip = "" ) {

	if ( !$ip ) {
		if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		} elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip=$_SERVER['REMOTE_ADDR'];
		}
	}
	$taxresponse = wp_remote_get( 'https://api.taxamo.com/api/v1/geoip/'.$ip.'?private_token=priv_test_NB4VjQy-7kTuMqzWvYBpQNvyHeK-n4S9Yt-NMu4GJ6I' );

	if( ! is_wp_error( $taxresponse )
		&& isset( $taxresponse['response']['code'] )        
		&& 200 === $taxresponse['response']['code'] )
	{
		$body = wp_remote_retrieve_body( $taxresponse );
		$bodyarray = json_decode( $body ); 
		return $bodyarray;
	}
}
?>
