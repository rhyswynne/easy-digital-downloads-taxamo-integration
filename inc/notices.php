<?php

/**
 * Check if Taxamo Keys are present, and tax is switched on. If not, display admin notices.
 *
 * @global Array  $edd_options    Array of all the EDD Options
 * @return [type] [description]
 */
function taxedd_add_admin_notices() {

	if ( function_exists( 'edd_use_taxes' ) && !edd_use_taxes() ) {
		add_action( 'admin_notices', 'taxedd_enable_tax_notice' );
	}

} add_action( 'admin_init', 'taxedd_add_admin_notices' );



function taxedd_enable_tax_notice() {

	$url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=taxes');  
?>
	<div class="error">
		<p><?php _e( 'Taxamo Integration for Easy Digital Downloads needs Taxes to be Enabled. <a href="'.$url.'">Click Here to enable Taxes</a>.'  , 'my-text-domain' ); ?></p>
	</div>
	<?php
}

?>
