<?php 
/**
 * Add the Taxamo Integration settings to EDD's settings array.
 * @param  Array $settings 	Array of all the EDD options
 * @return Array           	Array of all the EDD options with the Taxamo Integration Settings
 */
function taxedd_license_settings( $settings ) {

	$taxamo_settings = array(
		array(
			'id' => 'taxedd_header',
			'name' => '<strong>' . __( 'Taxamo Integration', 'taxamoedd' ) . '</strong>',
			'desc' => '',
			'type' => 'header',
			'size' => 'regular'
		),
		array(
			'id' => 'taxedd_public_key',
			'name' => __( 'Taxamo Public Key', 'taxamoedd' ),
			'desc' => __( 'Available from <a href="http://www.taxamo.com/">Taxamo</a>.', 'taxamoedd' ),
			'type' => 'text',
			'size' => 'large',
			'std'  => __( '', 'taxamoedd' )
		),
		array(
			'id' => 'taxedd_private_key',
			'name' => __( 'Taxamo Private Key', 'taxamoedd' ),
			'desc' => __( 'Available from <a href="http://www.taxamo.com/">Taxamo</a>.', 'taxamoedd' ),
			'type' => 'text',
			'size' => 'large',
			'std'  => __( '', 'taxamoedd' )
		),
		array(
			'id' => 'taxedd_custom_id_format',
			'name' => __( 'Custom ID Format', 'taxamoedd' ),
			'desc' => __( 'Format of the Custom ID.<br/>The string %%ID%% is replaced with the payment ID.', 'taxamoedd' ),
			'type' => 'text',
			'size' => 'large',
			'std'  => __( '%%ID%%', 'taxamoedd' )
		),
		array(
			'id' => 'taxedd_custom_invoice_format',
			'name' => __( 'Custom Invoice Format', 'taxamoedd' ),
			'desc' => __( 'Format of the Invoice Number.<br/>The string %%ID%% is replaced with the payment ID.', 'taxamoedd' ),
			'type' => 'text',
			'size' => 'large',
			'std'  => __( '%%ID%%', 'taxamoedd' )
		),
		array(
			'id' => 'taxedd_introduction_text',
			'name' => __( 'Introduction Header Text', 'taxamoedd' ),
			'desc' => __( 'This text will be added before the extra fields. Use this to link to your privacy policy and why you need this information', 'taxamoedd' ),
			'type' => 'rich_editor',
			'size' => 'large',
			'std'  => __( '', 'taxamoedd' )
		),
	);

	return array_merge( $settings, $taxamo_settings );

}
add_filter('edd_settings_extensions', 'taxedd_license_settings');