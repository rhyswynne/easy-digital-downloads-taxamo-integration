<?php

/**
 *
 *
 * @package
 * @version
 */
/*
Plugin Name: Taxamo Integration For Easy Digital Downloads
Plugin URI:
Description: Integrate Taxamo into Easy Digital Downloads. Make yourself Compatible with the VATMOSS EU Legislation
Version: 1.0
Author: Winwar Media
Author URI: http://winwar.co.uk/
Tags:
License: GPLv2 or later
Text Domain: taxamoedd
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// DEFINITIONS
define( 'TAXEDD_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'TAXEDD_PLUGIN_URL', plugins_url( '', __FILE__ ) );

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'TAXEDD_SL_STORE_URL', 'http://winwar.co.uk' ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system

// the name of your product. This is the title of your product in EDD and should match the download title in EDD exactly
define( 'TAXEDD_SL_ITEM_NAME', 'Taxamo Integration For Easy Digital Downloads' ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system

require_once(TAXEDD_PLUGIN_PATH . '/inc/core.php');


// retrieve our license key from the DB
$license_key = trim( get_option( 'taxedd_license_key' ) );

// setup the updater
$edd_updater = new EDD_SL_Plugin_Updater( TAXEDD_SL_STORE_URL, __FILE__, array( 
		'version' 	=> '1.0', 		// current version number
		'license' 	=> $license_key, 	// license key (used get_option above to retrieve from DB)
		'item_name'     => TAXEDD_SL_ITEM_NAME, 	// name of this plugin
		'author' 	=> 'Winwar Media',  // author of this plugin
		'url'           => home_url()
	)
);