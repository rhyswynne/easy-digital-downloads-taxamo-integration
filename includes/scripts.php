<?php
/**
 * Scripts
 *
 * @package     EDD\PluginName\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_taxamo_edd_integration_scripts( $hook ) {

    //wp_enqueue_script( 'taxamo-js-api', 'https://api.taxamo.com/js/v1/taxamo.all.js' );
    wp_enqueue_script( 'taxamo-custom-js', EDD_TAXAMOEDDINTEGRATION_URL . '/assets/js/scripts.js' );

}
add_action( 'wp_enqueue_scripts', 'edd_taxamo_edd_integration_scripts' );
?>