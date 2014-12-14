<?php

// API STUFF
require_once(TAXEDD_PLUGIN_PATH . '/inc/api/Taxamo.php');
require_once(TAXEDD_PLUGIN_PATH . '/inc/api/enqueue-js.php');
require_once(TAXEDD_PLUGIN_PATH . '/inc/api/Taxamo/models/input_transaction_line.php');
require_once(TAXEDD_PLUGIN_PATH . '/inc/api/Taxamo/models/input_transaction.php');

// EDD STUFF
require_once(TAXEDD_PLUGIN_PATH . '/inc/edd/added-fields.php');
require_once(TAXEDD_PLUGIN_PATH . '/inc/edd/post-purchase.php');
require_once(TAXEDD_PLUGIN_PATH . '/inc/edd/settings.php');

// MISC STUFF
require_once(TAXEDD_PLUGIN_PATH . '/inc/functions.php');
require_once(TAXEDD_PLUGIN_PATH . '/inc/notices.php');
?>