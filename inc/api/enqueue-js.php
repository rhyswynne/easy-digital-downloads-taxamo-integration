<?php

function taxedd_enqueue_js() {
	wp_enqueue_script( 'taxamo-js-api', 'https://api.taxamo.com/js/v1/taxamo.all.js' );
} add_action('wp_enqueue_scripts','taxedd_enqueue_js');

?>