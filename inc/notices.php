<?php

/**
 * Check if Taxamo Keys are present, and tax is switched on. If not, display admin notices.
 *
 * @global Array  $edd_options    Array of all the EDD Options
 * @return void
 */
function taxedd_add_admin_notices() {
	global $edd_options;

	if ( function_exists( 'edd_use_taxes' ) && !edd_use_taxes() ) {
		add_action( 'admin_notices', 'taxedd_enable_tax_notice' );
	}

	if ( !isset( $edd_options['taxedd_public_key'] ) || empty( $edd_options['taxedd_public_key'] ) || "" === $edd_options['taxedd_public_key'] || !isset( $edd_options['taxedd_private_key'] ) || empty( $edd_options['taxedd_private_key'] ) || "" === $edd_options['taxedd_private_key'] ) {
		add_action( 'admin_notices', 'taxedd_add_keys_notices' );
	}

} add_action( 'admin_init', 'taxedd_add_admin_notices' );


/**
 * Add a notice to enable tax if switched off.
 *
 * @return void
 */
function taxedd_enable_tax_notice() {

	$url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=taxes' );
	?>
	<div class="error">
		<p><?php _e( 'Taxamo Integration for Easy Digital Downloads needs Taxes to be Enabled. <a href="'.$url.'">Click Here to enable Taxes</a>.'  , 'taxamoedd' ); ?></p>
	</div>
	<?php
}


/**
 * Add a notice to add private and public keys.
 *
 * @return void
 */
function taxedd_add_keys_notices() {
	$url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions' );
	?>
	<div class="error">
		<p><?php _e( 'You need to add the Taxamo Public & Private Keys to the extension. <a href="'.$url.'">Click Here to add these fields</a>.'  , 'taxamoedd' ); ?></p>
	</div>
	<?php

}
?>
