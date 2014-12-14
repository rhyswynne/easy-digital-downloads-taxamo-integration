<?php


/************************************
* the code below is just a standard
* options page. Substitute with
* your own.
*************************************/

function taxamo_edd_license_menu() {
	add_plugins_page( 'Taxamo EDD License', 'Taxamo Integration with Easy Digital Downloads Plugin License', 'manage_options', 'taxamo-edd-licence', 'taxedd_license_page' );
}
add_action( 'admin_menu', 'taxamo_edd_license_menu' );

function taxedd_license_page() {
	$license  = get_option( 'taxedd_license_key' );
	$status  = get_option( 'taxedd_license_status' );
?>
	<div class="wrap">
		<h2><?php _e( 'Plugin License Options', 'taxamoedd' ); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields( 'taxedd_license' ); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'License Key' ); ?>
						</th>
						<td>
							<input id="taxedd_license_key" name="taxedd_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="taxedd_license_key"><?php _e( 'Enter your license key' ); ?></label>
						</td>
					</tr>
					<?php if ( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e( 'Activate License' ); ?>
							</th>
							<td>
								<?php if ( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e( 'active' ); ?></span>
									<?php wp_nonce_field( 'taxedd_nonce', 'taxedd_nonce' ); ?>
									<input type="submit" class="button-secondary" name="taxedd_deactivate" value="<?php _e( 'Deactivate License' ); ?>"/>
								<?php } else {
			wp_nonce_field( 'taxedd_nonce', 'taxedd_nonce' ); ?>
									<input type="submit" class="button-secondary" name="taxedd_activate" value="<?php _e( 'Activate License' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function taxedd_register_option() {
	// creates our settings in the options table
	register_setting( 'taxedd_license', 'taxedd_license_key', 'taxedd_validate_license' );
}
add_action( 'admin_init', 'taxedd_register_option' );

function taxedd_validate_license( $new ) {
	$old = get_option( 'taxedd_license_key' );
	if ( $old && $old != $new ) {
		delete_option( 'taxedd_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}



/************************************
* this illustrates how to activate
* a license key
*************************************/

function taxedd_activate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['taxedd_activate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'taxedd_nonce', 'taxedd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'taxedd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license'  => $license,
			'item_name' => urlencode( TAXEDD_SL_ITEM_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, TAXEDD_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'taxedd_license_status', $license_data->license );

	}
}
add_action( 'admin_init', 'taxedd_activate_license' );


/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

function taxedd_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['taxedd_deactivate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'taxedd_nonce', 'taxedd_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'taxedd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license'  => $license,
			'item_name' => urlencode( TAXEDD_SL_ITEM_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, TAXEDD_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data->license == 'deactivated' )
			delete_option( 'taxedd_license_status' );

	}
}
add_action( 'admin_init', 'taxedd_deactivate_license' );


/************************************
* this illustrates how to check if
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function taxedd_check_license() {

	global $wp_version;

	$license = trim( get_option( 'taxedd_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( TAXEDD_SL_ITEM_NAME ),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, TAXEDD_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );


	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
	print_r( $license_data );
}


?>
