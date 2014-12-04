<?php

/**
 *
 *
 * @package
 * @version
 */
/*
Plugin Name: Taxamo EDD Integration
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
define( 'TAXEDD_PUBLIC_KEY', 'public_test_p3kZrb-dDQucnUvRABY0Ajxkxyfqgs8Xb-2QJbPW9lE' );
define( 'TAXEDD_PRIVATE_KEY', 'priv_test__E8W36-LmVijPY6J4hb_zm4JW7bUdt_dX-CRgrtkOTI' );


require_once(TAXEDD_PLUGIN_PATH . '/inc/core.php');
