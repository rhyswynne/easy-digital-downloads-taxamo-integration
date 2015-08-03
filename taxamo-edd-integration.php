<?php
/**
 * Plugin Name:     Easy Digital Downloads - Taxamo Integration
 * Plugin URI:      http://winwar.co.uk/plugins/easy-digital-downloads-taxamo-integration/?utm_source=plugins-page&utm_medium=pluginlink&utm_campaign=eddtaxamointegration
 * Description:     Integrate Taxamo into Easy Digital Downloads. Make yourself Compatible with the VATMOSS EU Legislation
 * Version:         1.6
 * Author:          Winwar Media
 * Author URI:      http://winwar.co.uk/?utm_source=plugins-page&utm_medium=authorlink&utm_campaign=eddtaxamointegration
 * Text Domain:     taxamo-edd-integration
 *
 * @package         EDD\TaxamoEDDIntegration
 * @author          Winwar Media
 * @copyright       Copyright (c) 2014 Winwar Media
 *
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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'EDD_Taxamo_EDD_Integration' ) ) {

    /**
     * Main EDD_Taxamo_EDD_Integration class
     *
     * @since       1.0.0
     */
    class EDD_Taxamo_EDD_Integration {

        /**
         *
         *
         * @var         EDD_Taxamo_EDD_Integration $instance The one true EDD_Taxamo_EDD_Integration
         * @since       1.0.0
         */
        private static $instance;

        /**
         *
         *
         * @var         array $api_responses Stored responses from the Taxamo api
         * @since       1.3
         */
        public $api_responses = array();


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Taxamo_EDD_Integration
         */
        public static function instance() {
            if ( !self::$instance ) {
                self::$instance = new EDD_Taxamo_EDD_Integration();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_TAXAMOEDDINTEGRATION_VER', '1.6-beta' );

            // Plugin path
            define( 'EDD_TAXAMOEDDINTEGRATION_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_TAXAMOEDDINTEGRATION_URL', plugin_dir_url( __FILE__ ) );

            // Integration Name
            define( 'EDD_TAXAMOEDDINTEGRATION_NAME', 'edd-taxamo-integration' );

            // Domain Name
            $url =  parse_url( get_home_url() );
            define( 'EDD_TAXAMOEDDINTEGRATION_DOMAIN', $url['host'] );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/scripts.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/functions.php';
            // require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/admin.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/Taxamo.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/Taxamo/models/input_transaction_line.php';
            require_once EDD_TAXAMOEDDINTEGRATION_DIR . 'includes/libraries/taxamo-api/Taxamo/models/input_transaction.php';

            /**
             *
             *
             * @todo  Integrate Functions
             */

        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

            global $edd_options;

            // EDD Hooks
            add_filter( 'edd_settings_taxes', array( $this, 'settings' ), 1 );

            add_filter( 'edd_get_cart_subtotal', array( $this, 'calculate_discounted_amount_filter' ), 10, 1 );
            add_filter( 'edd_get_cart_tax', array( $this, 'calculate_tax_filter' ), 10, 1 );

            add_action( 'edd_cc_billing_top', array( $this, 'include_introduction_paragraph' ) );
            add_action( 'edd_cc_billing_bottom', array( $this, 'include_confirmation_checkbox' ), 15, 2 );

            add_action( 'edd_purchase_form_user_info', array( $this, 'add_country_code' ) );

            add_filter( 'edd_checkout_error_checks', array( $this, 'check_self_declaration' ), 10, 2 );
            add_filter( 'edd_payment_meta', array( $this, 'store_eu_data' ) );
            add_action( 'edd_purchase_data_before_gateway', array( $this, 'modify_tax' ), 999999, 2 );
            //add_action( 'edd_purchase_data_before_gateway', array( $this, 'test_tax' ), 1000000, 2 );
            add_action( 'edd_complete_purchase', array( $this, 'submit_order_to_taxamo' ) );

            add_action( 'edd_update_payment_status', array( $this, 'submit_refund_to_taxamo' ), 200, 3 );

            // VAT NUMBER CHECK
            if ( isset( $edd_options['taxedd_add_vatnumber_boxes'] ) ) {

                add_action( 'edd_cc_billing_bottom', array( $this, 'include_vat_check' ), 10, 2 );
                add_filter( 'edd_checkout_error_checks', array( $this, 'check_vat_number' ), 10, 2 );
                add_action( 'edd_payment_personal_details_list', array( $this, 'view_order_vat_number' ), 10, 2 );

            }

            //add_action( 'plugins_loaded', array( $this, 'unset_sessions' ) );

            // Admin Hooks
            if ( function_exists( 'edd_use_taxes' ) && !edd_use_taxes() ) {
                add_action( 'admin_notices', array( $this, 'enable_tax_notice' ) );
            }

            if ( !isset( $edd_options['taxedd_public_token'] ) || empty( $edd_options['taxedd_public_token'] ) || "" === $edd_options['taxedd_public_token'] || !isset( $edd_options['taxedd_private_token'] ) || empty( $edd_options['taxedd_private_token'] ) || "" === $edd_options['taxedd_private_token'] ) {
                add_action( 'admin_notices', array( $this, 'add_keys_notices' ) );
            }

            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_support_links' ) );

        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_TAXAMOEDDINTEGRATION_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_plugin_name_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'taxamoedd' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'taxamoedd', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/taxamoedd/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/taxamoedd/ folder
                load_textdomain( 'taxamoedd', $mofile_global );
            } elseif ( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/taxamoedd/languages/ folder
                load_textdomain( 'taxamoedd', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'taxamoedd', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param array   $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
            $new_settings = array(
                array(
                    'id'   => 'taxedd_header',
                    'name' => '<strong>' . __( 'Taxamo Integration', 'taxamoedd' ) . '</strong>',
                    'desc' => '',
                    'type' => 'header',
                    'size' => 'regular'
                    ),
                array(
                    'id'   => 'taxedd_public_token',
                    'name' => __( 'Taxamo Public Token', 'taxamoedd' ),
                    'desc' => sprintf( __( 'Available from <a href="%s">Taxamo</a>.', 'taxamoedd' ), esc_url( 'http://winwar.co.uk/recommends/taxamo/' ) ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '', 'taxamoedd' )
                    ),
                array(
                    'id'   => 'taxedd_private_token',
                    'name' => __( 'Taxamo Private Token', 'taxamoedd' ),
                    'desc' => sprintf( __( 'Available from <a href="%s">Taxamo</a>.', 'taxamoedd' ), esc_url( 'http://winwar.co.uk/recommends/taxamo/' ) ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '', 'taxamoedd' )
                    ),
                array(
                    'id'   => 'taxedd_add_vatnumber_boxes',
                    'name' => __( 'Ask for VAT Number?', 'taxamoedd' ),
                    'desc' => __( 'If you wish to ask the user for a VAT number, please check this box.', 'taxamoedd' ),
                    'type' => 'checkbox'
                    ),
                array(
                    'id'   => 'taxedd_custom_id_format',
                    'name' => __( 'Custom ID Format', 'taxamoedd' ),
                    'desc' => __( 'Format of the Custom ID.<br/>The string %%ID%% is replaced with the payment ID.', 'taxamoedd' ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '%%ID%%', 'taxamoedd' )
                    ),
                array(
                    'id'   => 'taxedd_disable_custom_id',
                    'name' => __( 'Disable Custom ID', 'taxamoedd' ),
                    'desc' => __( 'Tick this box to disable the custom ID from being sent to Taxamo.', 'taxamoedd' ),
                    'type' => 'checkbox'
                    ),
                array(
                    'id'   => 'taxedd_custom_invoice_format',
                    'name' => __( 'Custom Invoice Format', 'taxamoedd' ),
                    'desc' => __( 'Format of the Invoice Number.<br/>The string %%ID%% is replaced with the payment ID.', 'taxamoedd' ),
                    'type' => 'text',
                    'size' => 'large',
                    'std'  => __( '%%ID%%', 'taxamoedd' )
                    ),
                array(
                    'id'   => 'taxedd_disable_custom_invoice',
                    'name' => __( 'Disable Custom Invoice', 'taxamoedd' ),
                    'desc' => __( 'Tick this box to disable the custom invoice from being sent to Taxamo, and allow the invoice number to be auto generated by Taxamo.', 'taxamoedd' ),
                    'type' => 'checkbox'
                    ),
                array(
                    'id'   => 'taxedd_introduction_text',
                    'name' => __( 'Introduction Header Text', 'taxamoedd' ),
                    'desc' => __( 'This text will be added before the extra fields. Use this to link to your privacy policy and why you need this information', 'taxamoedd' ),
                    'type' => 'rich_editor',
                    'size' => 'large',
                    'std'  => __( '', 'taxamoedd' )
                    ),
                );

return array_merge( $settings, $new_settings );
}

        /*
         * Activation function fires when the plugin is activated.
         *
         * This function is fired when the activation hook is called by WordPress,
         *
         */
        public static function activation() {
            /*Activation functions here*/

        }

        /**
         * Adds to the checkout field the hidden value the taxamo software connects to.
         *
         * @return void
         */
        public static function add_country_code() {

            $taxamo = taxedd_get_country_code();

            if ( $taxamo && isset( $taxamo->country_code ) ) {
                ?>
                <input class="edd-country" type="hidden" name="edd_country" id="edd-country" value="<?php echo $taxamo->country_code; ?>"/>
                <?php
            }
        }

        /**
         * Adds above the billing address an introductory paragraph, should it be present.
         *
         * @return void
         */
        public static function include_introduction_paragraph() {
            global $edd_options;

            if ( isset( $edd_options['taxedd_introduction_text'] ) && !empty( $edd_options['taxedd_introduction_text'] ) && "" !== $edd_options['taxedd_introduction_text'] ) {
                $intro_text = $edd_options['taxedd_introduction_text'];
                echo '<p>' . $intro_text . '</p>';
            }

        }


        /**
         * Adds a confirmation checkbox to the post. If billing data is present, and it matches IP address, then hide for now.
         *
         * @return void
         */
        public static function include_confirmation_checkbox() {

            $logged_in = is_user_logged_in();

            if( $logged_in ) {
                $user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );
            }

            $selected_country = edd_get_shop_country();
            $taxamo = taxedd_get_country_code();
            $stylecss = "";

            if( $logged_in && ! empty( $user_address['country'] ) && '*' !== $user_address['country'] ) {
                $selected_country = $user_address['country'];
            }

            if ( isset( $taxamo ) ) {
                if ( $taxamo->country_code == $selected_country ) {
                    $stylecss = ' style="display: none;"';
                }
            }

            ?>


            <p id="edd-confirmation-checkbox" <?php echo $stylecss; ?>>
                <label for="edd-vat-confirm" class="edd-label">
                    <?php _e( 'By clicking this checkbox, I can confirm my billing address is valid and is located in my usual country of residence.', 'taxamoedd' ); ?>
                    <input class="edd-self-declaration" type="checkbox" name="edd_self_declaration" id="edd-self-declaration" value="true" />
                </label>
                <?php
            }


        /**
         * Adds a checkbox to the site allowing users to check if they're registered for VAT in the EU.
         *
         * @return void
         */
        public static function include_vat_check() {
            ?>
            <p id="edd-vat-reg-check-wrap">
                <label for="edd-vatreg" class="edd-label">
                    <?php _e( 'I am registered for VAT in the EU', 'taxamoedd' ); ?>
                    <input class="edd-vatreg" type="checkbox" name="edd_vatreg" id="edd-vatreg" value="true" />
                </label>
            </p>

            <p id="edd-vat-reg-number-wrap">
                <label for="vat_number" class="edd-label">
                    <?php _e( 'VAT Number', 'taxamoedd' ); ?>
                </label>
                <span class="edd-description"><?php _e( 'If you are registered for VAT, place your VAT number here (no spaces).', 'taxamoedd' ); ?></span>
                <input type="text" id="vat_number" name="vat_number" class="vat-number edd-input" placeholder="<?php _e( 'VAT Number', 'taxamoedd' ); ?>" value=""/>
            </p>


            <?php
        }

        /**
         * Check the VAT Number is present if the checkbox is ticked.
         *
         * @return void
         */
        public static function check_vat_number( $valid_data, $data ) {
            global $edd_options;

            if ( isset( $data['edd_vatreg'] ) ) {
                if ( !isset( $data['vat_number'] ) || empty( $data['vat_number'] ) || "" === $data['vat_number'] ) {
                    edd_set_error( 'taxedd-no-vat-number-error', __( 'If you are VAT registered, please enter a VAT number.', 'taxamoedd' ) );
                }

                if ( isset( $edd_options['taxedd_private_token'] ) ) {

                    $vatnumber = $data['vat_number'];
                    $resp = taxedd_get_vat_details( $vatnumber );

                    if ( 1 != $resp['buyer_tax_number_valid'] ) {
                        edd_set_error( 'taxedd-invalid-vat-number', __( 'The VAT number is invalid. Please double check or untick the VAT Registered Box.', 'taxamoedd' ) );
                    }

                } else {
                    edd_set_error( 'taxedd-no-prviate-key', __( 'Private key not present, so unable to complete purchase. Please contact shop owner.', 'taxamoedd' ) );
                }
            }
        }


        /**
         * Check if the user's IP address location matches the billing address, if not, see if they've ticket the self declaration box.
         *
         * @return void
         */
        public static function check_self_declaration( $valid_data, $data ) {
            global $edd_options;

            if ( edd_get_cart_total() == 0 ) {
                return;
            }

            if ( isset( $data['edd_country'] ) ) {

                if ( $data['billing_country'] != $data['edd_country'] ) {

                    if ( !isset( $data['edd_self_declaration'] ) ) {
                        edd_set_error( 'taxedd-no-self-declaration', __( 'Please confirm that the billing address is correct.', 'taxamoedd' ) );
                    }

                }
            } else {

                // Failback if we're unable to get an IP address location
                if ( !isset( $data['edd_self_declaration'] ) ) {
                    edd_set_error( 'taxedd-no-self-declaration', __( 'Please confirm that the billing address is correct.', 'taxamoedd' ) );
                }

            }
        }


        /**
         * Stores the country code in the payment meta.
         *
         * @return void
         */
        public function store_eu_data( $payment_meta ) {
            global $edd_options;

            $payment_meta['country']    = isset( $_POST['edd_country'] ) ? sanitize_text_field( $_POST['edd_country'] ) : $payment_meta['user_info']['address']['country'];

            $payment_meta['edd_vatreg'] = isset( $_POST['edd_vatreg'] ) ? true : false;

            // Check if user is VAT Registered with a Valid number. If so, set the Tax to 0.
            if ( isset( $_POST['vat_number'] ) && !empty( $_POST['vat_number'] ) && "" !== $_POST['vat_number'] ) {

                $payment_meta['vat_number'] = preg_replace( '/\s+/', '', $_POST['vat_number'] );

                $vatarray = taxedd_get_vat_details( $payment_meta['vat_number'] );

                if ( isset( $vatarray['billing_country_code'] ) ) {
                    $payment_meta['vat_billing_country_code'] = $vatarray['billing_country_code'];
                } else {
                    $payment_meta['vat_billing_country_code'] = $payment_meta['user_info']['address']['country'];
                }
                // But if the base country is equal to the VAT Country code, add the tax on.
                if ( $edd_options['base_country'] == $payment_meta['vat_billing_country_code'] ) {
                    $payment_meta['tax'] = $this->calculate_tax( $payment_meta['vat_billing_country_code'] );
                }

            } else {
                $payment_meta['vat_number'] = "";
                $payment_meta['tax'] = $this->calculate_tax( $payment_meta['user_info']['address']['country'] );
            }

            // Set self declaration flag if needed.
            if ( isset( $_POST['edd_self_declaration'] ) ) {

                $payment_meta['self_declaration'] = esc_attr( $_POST['edd_self_declaration'] );

            }

            return $payment_meta;
        }



        /**
         * Add the Vat Number to View Order Details
         *
         * @param Array   $payment_meta The payment meta associated with this order.
         * @param Array   $user_info    The user information associated with this order.
         * @return void
         */
        public static function view_order_vat_number( $payment_meta, $user_info ) {
            $vatnumber = isset( $payment_meta['vat_number'] ) ? $payment_meta['vat_number'] : '';
            ?>
            <div class="column-container">
                <div class="column">
                    <strong><?php _e( 'VAT Number:', 'taxamoedd' ) ?></strong>&nbsp;
                    <input type="text" name="vatnumber" value="<?php esc_attr_e( $vatnumber ); ?>" class="medium-text" />
                    <p class="description"><?php _e( 'If the customer had a VAT number, it will be here', 'taxamoedd' ); ?></p>
                </div>
            </div>
            <?php
        }

        /**
         * Modify the tax if user has a VAT number.
         *
         * @param Array   $purchase_data Array of the purchase data
         * @param Array   $valid_data    Array of valid data
         * @return
         */
        public function modify_tax( $purchase_data, $valid_data ) {
            global $edd_options;

            $alreadyvatchecked = FALSE;

            // If Checkout doesn't include tax
            if ( "no" == $edd_options['checkout_include_tax'] ) {

                // Check if we have a Valid VAT number, if so, remove the tax.
                if ( isset( $purchase_data['post_data']['vat_number'] ) && !empty( $purchase_data['post_data']['vat_number'] ) && "" !== $purchase_data['post_data']['vat_number'] &&
                    $purchase_data['post_data']['edd_vatreg'] ) {
                    $vatarray = taxedd_get_vat_details( $purchase_data['post_data']['vat_number'] );

                if ( isset( $vatarray['billing_country_code'] ) ) {
                    $billingcc = $vatarray['billing_country_code'];
                } else {
                    $billingcc = $purchase_data['post_data']['billing_country'];
                }

                // Check if Base Country matches Billing Country, if not, remove the VAT
                if ( $edd_options['base_country'] !== $billingcc ) {

                    $old_data = $purchase_data;

                    $purchase_data['price'] = $purchase_data['price'] - $purchase_data['tax'];
                    $purchase_data['tax'] = 0;
                    $purchase_data['vat_billing_country_code'] = $billingcc;

                    $alreadyvatchecked = TRUE;


                } else {

                // Just double check tax again, with the new Billing Country Code
                    $purchase_data['price'] = $purchase_data['price'] - $purchase_data['tax'];
                    $purchase_data['tax'] = $this->calculate_tax( $billingcc );
                    $purchase_data['price'] = $purchase_data['price'] + $purchase_data['tax'];
                    

                    $alreadyvatchecked = TRUE;

                }
            }

            // If there's a self declaration flag AND we haven't already calculated tax, calculate tax.
            if ( isset( $purchase_data['post_data']['edd_self_declaration'] ) && !$alreadyvatchecked ) {

                if ( $purchase_data['post_data']['edd_self_declaration'] ) {

                    $purchase_data['price'] = $purchase_data['price'] - $purchase_data['tax'];
                    $purchase_data['tax'] = $this->calculate_tax( $purchase_data['user_info']['address']['country'] );
                    $purchase_data['price'] = $purchase_data['price'] + $purchase_data['tax'];

                }
            }
        }

        // Now do checks if we include tax
        if ( "yes" == $edd_options['checkout_include_tax'] ) {

            // Check if we have a Valid VAT number, if so, remove the tax.
            if ( isset( $purchase_data['post_data']['vat_number'] ) && !empty( $purchase_data['post_data']['vat_number'] ) && "" !== $purchase_data['post_data']['vat_number'] &&
                $purchase_data['post_data']['edd_vatreg'] ) {

                $vatarray = taxedd_get_vat_details( $purchase_data['post_data']['vat_number'] );

            if ( isset( $vatarray['billing_country_code'] ) ) {
                $billingcc = $vatarray['billing_country_code'];
            } else {
                $billingcc = $purchase_data['post_data']['billing_country'];
            }

                // Check if Base Country matches Billing Country, if not, remove the VAT
            if ( $edd_options['base_country'] !== $billingcc ) {

                $old_data = $purchase_data;

                $oldtax = $purchase_data['tax'];
                $purchase_data['price'] = $purchase_data['price'] - $purchase_data['tax'];
                $purchase_data['tax'] = 0;
                $purchase_data['vat_billing_country_code'] = $billingcc;

                $tax_rate_array = $this->get_api_response_calculate_tax_rate( $purchase_data['user_info']['address']['country'] );

                $totalsubtotal  = 0;
                $totaltax       = 0;
                $totalprice     = 0;
                $totaldiscount  = 0;

                for ( $x = 0; $x < sizeof( $purchase_data['cart_details'] ); $x++ ) {

                    $tax = $tax_rate_array[ $x ];
                    $var = '1.' . $tax;
                    $float = floatval( $var );

                    $old_item_price = $purchase_data['cart_details'][$x]['item_price'];
                    $new_item_price = $purchase_data['cart_details'][$x]['item_price'] / $float;
                    $tax_deducted   = $old_item_price - $new_item_price;

                    $purchase_data['cart_details'][$x]['item_price']    = round( $new_item_price, edd_currency_decimal_filter() );
                    $purchase_data['cart_details'][$x]['discount']      = edd_get_cart_item_discount_amount( $purchase_data['cart_details'][$x]['item_number'] );
                    $purchase_data['cart_details'][$x]['tax']           = 0.0;
                    $purchase_data['cart_details'][$x]['subtotal']      = round( $purchase_data['cart_details'][$x]['item_price'] * $purchase_data['cart_details'][$x]['quantity'], edd_currency_decimal_filter() );
                    $purchase_data['cart_details'][$x]['price']         = round( $purchase_data['cart_details'][$x]['subtotal'] - $purchase_data['cart_details'][$x]['discount'] + $purchase_data['cart_details'][$x]['tax'], edd_currency_decimal_filter() );

                    $totalsubtotal                                      = $totalsubtotal + $purchase_data['cart_details'][$x]['subtotal'];
                    $totaltax                                           = $totaltax + $purchase_data['cart_details'][$x]['tax'];
                    $totalprice                                         = $totalprice + $purchase_data['cart_details'][$x]['price'];
                    $totaldiscount                                      = $totaldiscount + $purchase_data['cart_details'][$x]['discount'];

                }

                $purchase_data['subtotal']  = $totalsubtotal;
                $purchase_data['discount']  = $totaldiscount;
                $purchase_data['tax']       = $totaltax;
                $purchase_data['price']     = $totalprice;

                $alreadyvatchecked = TRUE;

            } else {

                    /* Just double check tax again, with the new Billing Country Code
                    $purchase_data['price'] = $purchase_data['price'] - $purchase_data['tax'];
                    $purchase_data['tax'] = $this->calculate_tax( $billingcc );
                    $purchase_data['price'] = $purchase_data['price'] + $purchase_data['tax'];
                    */

                    $tax_rate_array = $this->get_api_response_calculate_tax_rate();

                    $totalsubtotal  = 0;
                    $totaltax       = 0;
                    $totalprice     = 0;
                    $totaldiscount  = 0;

                    for ( $x = 0; $x < sizeof( $purchase_data['cart_details'] ); $x++ ) {

                        $tax = $tax_rate_array[ $x ];
                        $var = '1.' . $tax;
                        $float = floatval( $var );

                        $old_item_price = $purchase_data['cart_details'][$x]['item_price'];
                        $new_item_price = $purchase_data['cart_details'][$x]['item_price'] / $float;
                        $tax_deducted   = $old_item_price - $new_item_price;

                        $purchase_data['cart_details'][$x]['item_price']    = round( $new_item_price, edd_currency_decimal_filter() );
                        $purchase_data['cart_details'][$x]['discount']      = edd_get_cart_item_discount_amount( $purchase_data['cart_details'][$x]['item_number'] );
                        $purchase_data['cart_details'][$x]['tax']           = round( $tax_deducted, edd_currency_decimal_filter() );
                        $purchase_data['cart_details'][$x]['subtotal']      = round( $purchase_data['cart_details'][$x]['item_price'] * $purchase_data['cart_details'][$x]['quantity'], edd_currency_decimal_filter() );
                        $purchase_data['cart_details'][$x]['price']         = round( $purchase_data['cart_details'][$x]['subtotal'] - $purchase_data['cart_details'][$x]['discount'] + $purchase_data['cart_details'][$x]['tax'], edd_currency_decimal_filter() );

                        $totalsubtotal                                      = $totalsubtotal + $purchase_data['cart_details'][$x]['subtotal'];
                        $totaltax                                           = $totaltax + $purchase_data['cart_details'][$x]['tax'];
                        $totalprice                                         = $totalprice + $purchase_data['cart_details'][$x]['price'];
                        $totaldiscount                                      = $totaldiscount + $purchase_data['cart_details'][$x]['discount'];

                    }

                    $purchase_data['subtotal']  = $totalsubtotal;
                    $purchase_data['discount']  = $totaldiscount;
                    $purchase_data['tax']       = $totaltax;
                    $purchase_data['price']     = $totalprice;

                    $alreadyvatchecked = TRUE;

                }
            }

            // If there's a self declaration flag AND we haven't already calculated tax, calculate tax.
            if ( isset( $purchase_data['post_data']['edd_self_declaration'] ) && !$alreadyvatchecked ) {

                if ( $this->check_if_eu( $purchase_data['user_info']['address']['country'] ) ) {

                    if ( $purchase_data['post_data']['edd_self_declaration'] ) {

                        $tax_rate_array = $this->get_api_response_calculate_tax_rate( $purchase_data['user_info']['address']['country'] );


                        $totalsubtotal  = 0;
                        $totaltax       = 0;
                        $totalprice     = 0;
                        $totaldiscount  = 0;

                        for ( $x = 0; $x < sizeof( $purchase_data['cart_details'] ); $x++ ) {

                            $tax = $tax_rate_array[ $x ];
                            $var = '1.' . $tax;
                            $float = floatval( $var );

                            $old_item_price = $purchase_data['cart_details'][$x]['item_price'];
                            $new_item_price = $purchase_data['cart_details'][$x]['item_price'] / $float;
                            $tax_deducted   = $old_item_price - $new_item_price;

                            $purchase_data['cart_details'][$x]['item_price']    = round( $new_item_price, edd_currency_decimal_filter() );
                            $purchase_data['cart_details'][$x]['discount']      = edd_get_cart_item_discount_amount( $purchase_data['cart_details'][$x]['item_number'] );
                            $purchase_data['cart_details'][$x]['tax']           = round( $tax_deducted, edd_currency_decimal_filter() );
                            $purchase_data['cart_details'][$x]['subtotal']      = round( $purchase_data['cart_details'][$x]['item_price'] * $purchase_data['cart_details'][$x]['quantity'], edd_currency_decimal_filter() );
                            $purchase_data['cart_details'][$x]['price']         = round( $purchase_data['cart_details'][$x]['subtotal'] - $purchase_data['cart_details'][$x]['discount'] + $purchase_data['cart_details'][$x]['tax'], edd_currency_decimal_filter() );

                            $totalsubtotal                                      = $totalsubtotal + $purchase_data['cart_details'][$x]['subtotal'];
                            $totaltax                                           = $totaltax + $purchase_data['cart_details'][$x]['tax'];
                            $totalprice                                         = $totalprice + $purchase_data['cart_details'][$x]['price'];
                            $totaldiscount                                      = $totaldiscount + $purchase_data['cart_details'][$x]['discount'];

                        }

                        $purchase_data['subtotal']  = $totalsubtotal;
                        $purchase_data['discount']  = $totaldiscount;
                        $purchase_data['tax']       = $totaltax;
                        $purchase_data['price']     = $totalprice;
                    }

                }

                $alreadyvatchecked = TRUE;
            }

            if ( !$alreadyvatchecked ) { 

                if ( $this->check_if_eu( $purchase_data['user_info']['address']['country'] ) ) {

                    $tax_rate_array = $this->get_api_response_calculate_tax_rate();

                    $totalsubtotal  = 0;
                    $totaltax       = 0;
                    $totalprice     = 0;
                    $totaldiscount  = 0;

                    for ( $x = 0; $x < sizeof( $purchase_data['cart_details'] ); $x++ ) {

                        $tax = $tax_rate_array[ $x ];
                        $var = '1.' . $tax;
                        $float = floatval( $var );

                        $old_item_price = $purchase_data['cart_details'][$x]['item_price'];
                        $new_item_price = $purchase_data['cart_details'][$x]['item_price'] / $float;
                        $tax_deducted   = $old_item_price - $new_item_price;

                        $purchase_data['cart_details'][$x]['item_price']    = round( $new_item_price, edd_currency_decimal_filter() );
                        $purchase_data['cart_details'][$x]['discount']      = edd_get_cart_item_discount_amount( $purchase_data['cart_details'][$x]['item_number'] );
                        $purchase_data['cart_details'][$x]['tax']           = round( $tax_deducted, edd_currency_decimal_filter() );
                        $purchase_data['cart_details'][$x]['subtotal']      = round( $purchase_data['cart_details'][$x]['item_price'] * $purchase_data['cart_details'][$x]['quantity'], edd_currency_decimal_filter() );
                        $purchase_data['cart_details'][$x]['price']         = round( $purchase_data['cart_details'][$x]['subtotal'] - $purchase_data['cart_details'][$x]['discount'] + $purchase_data['cart_details'][$x]['tax'], edd_currency_decimal_filter() );

                        $totalsubtotal                                      = $totalsubtotal + $purchase_data['cart_details'][$x]['subtotal'];
                        $totaltax                                           = $totaltax + $purchase_data['cart_details'][$x]['tax'];
                        $totalprice                                         = $totalprice + $purchase_data['cart_details'][$x]['price'];
                        $totaldiscount                                      = $totaldiscount + $purchase_data['cart_details'][$x]['discount'];

                    }

                    $purchase_data['subtotal']  = $totalsubtotal;
                    $purchase_data['discount']  = $totaldiscount;
                    $purchase_data['tax']       = $totaltax;
                    $purchase_data['price']     = $totalprice;

                }
            }
        }

        return $purchase_data;
    }

        /**
         *
         *
         * @param int     $payment_id ID of the EDD order just completed.
         * @global Array $edd_options   Array of all the EDD Options
         * @return void
         */
        public static function submit_order_to_taxamo( $payment_id ) {
            global $edd_options;

            if ( edd_get_payment_meta( $payment_id, '_edd_payment_total', true ) == 0 ) {
                return;
            }

            if ( isset( $edd_options['taxedd_private_token'] ) ) {

                $private_key = $edd_options['taxedd_private_token'];

                if ( !isset( $edd_options['taxedd_disable_custom_id'] ) ) {
                    $custom_id = "";
                    $custom_id = $edd_options['taxedd_custom_id_format'];
                    $custom_id = str_replace( '%%ID%%', $payment_id, $custom_id );
                }

                if ( !isset( $edd_options['taxedd_disable_custom_invoice'] ) ) {
                    $custom_invoice = "";
                    $custom_invoice = $edd_options['taxedd_custom_invoice_format'];
                    $custom_invoice = str_replace( '%%ID%%', $payment_id, $custom_invoice );
                }


                try {

                    $apiclient = new APIClient( $private_key, 'https://api.taxamo.com' );
                    $apiclient->sourceId = '['.EDD_TAXAMOEDDINTEGRATION_DOMAIN.'-'.EDD_TAXAMOEDDINTEGRATION_NAME.'-'. EDD_TAXAMOEDDINTEGRATION_VER.']';

                    $taxamo = new Taxamo( $apiclient );

                    // Basic payment meta
                    $payment_meta = edd_get_payment_meta( $payment_id );

                    // Cart details
                    $cart_items = edd_get_payment_meta_cart_details( $payment_id );

                    $ip = edd_get_payment_user_ip( $payment_id );

                    $date = strtotime( $payment_meta['date'] );

                    $transactionarray = array();

                    $productid = 1;

                    if ( !isset( $payment_meta['vat_number'] ) || empty( $payment_meta['vat_number'] ) || "" === $payment_meta['vat_number'] ) {

                        foreach ( $payment_meta['cart_details'] as $cart_detail ) {
                            $transaction_line = new Input_transaction_line();

                            if ( "yes" == $edd_options['checkout_include_tax'] ) { 
                                $transaction_line->total_amount = $cart_detail['price'];
                            } else {
                                $transaction_line->amount = $cart_detail['price'];
                            }

                            $transaction_line->custom_id = $cart_detail['name'] . $productid;
                            $transaction_line->description = $cart_detail['name'];
                            array_push( $transactionarray, $transaction_line );
                            $productid++;

                        }
                    }

                    $transaction = new Input_transaction();

                    $transaction->currency_code = $payment_meta['currency'];
                    $transaction->buyer_ip = $ip;
                    $transaction->billing_country_code = $payment_meta['user_info']['address']['country'];
                    $transaction->buyer_email = $payment_meta['email'];
                    $transaction->original_transaction_key = $payment_meta['key'];

                    if ( !isset( $edd_options['taxedd_disable_custom_id'] ) ) {
                        $transaction->custom_id = $custom_id;
                    }

                    if ( !isset( $edd_options['taxedd_disable_custom_invoice'] ) ) {
                        $transaction->invoice_number = $custom_invoice;
                    }

                    // If we have a self declaration, force the country code of the billing address.
                    if ( isset( $payment_meta['self_declaration'] ) ) {
                        $transaction->tax_country_code = $payment_meta['user_info']['address']['country'];
                        $transaction->force_country_code = $payment_meta['user_info']['address']['country'];
                    }

                    if ( isset( $payment_meta['vat_number'] ) && !empty( $payment_meta['vat_number'] ) && "" !== $payment_meta['vat_number'] ) {
                        $transaction->buyer_tax_number = $payment_meta['vat_number'];

                        // We don't deduct tax for VAT Registered sales within the same country.
                        if ( $payment_meta['vat_billing_country_code'] == $edd_options['base_country'] ) {

                            $transaction->tax_deducted = false;

                            foreach ( $payment_meta['cart_details'] as $cart_detail ) {

                                $transaction_line = new Input_transaction_line();

                                if ( "yes" == $edd_options['checkout_include_tax'] ) { 
                                    $transaction_line->total_amount = $cart_detail['price'];
                                } else {
                                    $transaction_line->amount = $cart_detail['price'];
                                }

                                $transaction_line->custom_id = $cart_detail['name'] . $productid;
                                $transaction_line->description = $cart_detail['name'];
                                array_push( $transactionarray, $transaction_line );
                                $productid++;

                            }

                        } else {

                            $transaction->tax_deducted = true;

                            foreach ( $payment_meta['cart_details'] as $cart_detail ) {

                                $transaction_line = new Input_transaction_line();
                                $transaction_line->amount = $cart_detail['price'];
                                $transaction_line->custom_id = $cart_detail['name'] . $productid;
                                $transaction_line->description = $cart_detail['name'];
                                array_push( $transactionarray, $transaction_line );
                                $productid++;

                            }

                        }
                    }

                    // Set Username
                    if ( isset( $payment_meta['user_info']['first_name'] ) && isset( $payment_meta['user_info']['last_name'] ) ) {
                        $transaction->buyer_name = $payment_meta['user_info']['first_name'] . " " . $payment_meta['user_info']['last_name'];
                    }

                    // Build The Address
                    $address = array();

                    if ( isset( $payment_meta['user_info']['address']['line1'] ) && !empty( $payment_meta['user_info']['address']['line1'] )  && "" !== ( $payment_meta['user_info']['address']['line1'] ) ) {
                        $streename = array( "street_name"=>$payment_meta['user_info']['address']['line1'] );
                        $address = array_merge( $streename, $address );
                    }

                    if ( isset( $payment_meta['user_info']['address']['line2'] ) && !empty( $payment_meta['user_info']['address']['line2'] )  && "" !== ( $payment_meta['user_info']['address']['line2'] ) ) {
                        $addressdetailname = array( "address_detail"=>$payment_meta['user_info']['address']['line2'] );
                        $address = array_merge( $addressdetailname, $address );
                    }

                    if ( isset( $payment_meta['user_info']['address']['city'] ) && !empty( $payment_meta['user_info']['address']['city'] ) && "" !== ( $payment_meta['user_info']['address']['city'] ) ) {
                        $cityname = array( "city"=>$payment_meta['user_info']['address']['city'] );
                        $address = array_merge( $cityname, $address );
                    }

                    if ( isset( $payment_meta['user_info']['address']['state'] ) && !empty( $payment_meta['user_info']['address']['state'] )  && "" !== ( $payment_meta['user_info']['address']['state'] ) ) {
                        $regionname = array( "region"=>$payment_meta['user_info']['address']['state'] );
                        $address = array_merge( $regionname, $address );
                    }

                    if ( isset( $payment_meta['user_info']['address']['zip'] ) && !empty( $payment_meta['user_info']['address']['zip'] )  && "" !== ( $payment_meta['user_info']['address']['zip'] ) ) {
                        $postalcode = array( "postal_code"=>$payment_meta['user_info']['address']['zip'] );
                        $address = array_merge( $postalcode, $address );
                    }

                    $transaction->invoice_address = $address;

                    $transaction->invoice_date = date( "Y-m-d", $date );

                    $transaction->transaction_lines = $transactionarray;

                    $resp = $taxamo->createTransaction( array( 'transaction' => $transaction ) );

                    $taxamo->confirmTransaction( $resp->transaction->key, array( 'transaction' => $transaction ) );

                    $transactionkey = array( 'taxamo_transaction_key' => $resp->transaction->key );
                    $transactionlines = array();

                    // Get all Transaction Lines and add keys to site.
                    foreach ( $resp->transaction->transaction_lines as $transaction_line ) {

                        $temptransactionline = array(
                            "taxamo_line_key"=>$transaction_line->line_key,
                            "taxamo_total_amount"=>$transaction_line->total_amount
                            );

                        array_push( $transactionlines, $temptransactionline );

                    }

                    $payment_meta = array_merge( $payment_meta, $transactionkey );
                    $payment_meta = array_merge( $payment_meta,
                        array( 'taxamo_transaction_lines' => $transactionlines ) );

                } catch ( Exception $e ) {

                    $note = "Unable to submit order to Taxamo. Reason: " . $e->getMessage();
                    edd_insert_payment_note( $payment_id, $note );
                    $error = array( 'taxamo_unsubmitted' => true );
                    $payment_meta = array_merge( $payment_meta, $error );

                }

                update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );

            }

        }


        /**
         * Calculate discounted amount. If inclusive of tax then we take it off
         * @param  string   $cartamount     the current cart amount
         * @return string                   the current cart amount (with tax removed)
         */
        function calculate_discounted_amount_filter( $cartamount ) {
            global $edd_options;

            if ( "yes" == $edd_options['checkout_include_tax'] ) {

                $logged_in = is_user_logged_in();

                $country = "";

                if( $logged_in ) {
                    $user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );
                    if ( !empty( $user_address['country'] ) && '*' !== $user_address['country'] ) {
                        $country = $user_address['country'];                        
                    }
                }

                if ( !$country ) {
                    $taxamo = taxedd_get_country_code();
                    $country = $taxamo->country_code;
                }

                if ( $this->check_if_eu( $country ) ) {
                    $tax = edd_get_cart_tax();
                    $cartamount = $cartamount - $tax;
                }

            }

            return $cartamount;
        }

        /**
         * Filter to display tax. This is done based on address, if not present it's done on IP address.
         *
         * @return void
         */
        public function calculate_tax_filter( $carttax ) {
            global $edd_options;

            if ( isset( $edd_options['taxedd_private_token'] ) ) {

                $tax = $this->get_api_response_calculate_tax();

                if ( $tax != 0 ) {
                    return $tax;
                } else {
                    return $carttax;
                }
            }
        }


        /**
         * Function to calculate tax, usually used for VAT calculation.
         *
         * @param string  $countrycode 2 Letter Country Code
         * @return float  $resp->transaction->tax_amount the amount of tax paid.
         */
        public function calculate_tax( $countrycode = "" ) {
            global $edd_options;

            if ( isset( $edd_options['taxedd_private_token'] ) ) {
                $tax = $this->get_api_response_calculate_tax( $countrycode );

                if ( $tax != 0 || $this->check_if_eu( $countrycode ) ) {
                    return $tax;
                } else {
                    return edd_get_cart_tax();
                }

            }
        }


        /**
         * Add a notice to enable tax if switched off.
         *
         * @return void
         */
        function enable_tax_notice() {

            $url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=taxes' );
            ?>
            <div class="error">
                <p><?php echo sprintf( __( 'Taxamo Integration needs Taxes to be Enabled. <a href="%s">Click Here to enable Taxes</a>.', 'taxamoedd' ), esc_url( $url ) ); ?></p>
            </div>
            <?php
        }


        /**
         * Add a notice to add private and public keys.
         *
         * @return void
         */
        function add_keys_notices() {
            $url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=taxes' );
            ?>
            <div class="error">
                <p><?php echo sprintf( __( 'You need to add the Taxamo Public & Private Keys to the extension. <a href="%s"><strong>Sign Up for Taxamo</strong></a> and then <a href="%s">Click Here to add these fields</a>.', 'taxamoedd' ), esc_url( 'http://winwar.co.uk/recommends/taxamo' ), esc_url( $url ) ); ?></p>
            </div>
            <?php

        }


        /**
         * Submit the refund to Taxamo when refunded.
         *
         * @param int     $payment_id ID for the refund.
         * @param string  $new_status The new status
         * @param string  $old_status The old status
         * @return void
         */
        function submit_refund_to_taxamo( $payment_id, $new_status, $old_status ) {

            global $edd_options;

            if ( 'refunded' != $new_status )
                return;


            // Get Taxamo Tansaction Key.
            $payment_meta = edd_get_payment_meta( $payment_id );
            $taxamo_transaction_lines = "";
            $taxamo_transaction_key = "";

            if ( isset( $payment_meta['taxamo_transaction_key'] ) ) {

                $taxamo_transaction_lines = $payment_meta['taxamo_transaction_lines'];
                $transaction_key = $payment_meta['taxamo_transaction_key'];

                // Get Order Total and create an array for it.
                foreach ( $taxamo_transaction_lines as $taxamo_transaction_line ) {
                    $line_key = $taxamo_transaction_line['taxamo_line_key'];
                    $amount = $taxamo_transaction_line['taxamo_total_amount'];

                    $taxamo_body_array = array( "total_amount"=>$amount,
                        "line_key" => $line_key );
                    $taxamo_body_json = json_encode( $taxamo_body_array );

                // Create Taxamo Object and Submit a refund
                    $private_key = $edd_options['taxedd_private_token'];

                    $apiclient = new APIClient( $private_key, 'https://api.taxamo.com' );
                    $apiclient->sourceId = '['.EDD_TAXAMOEDDINTEGRATION_DOMAIN.'-'.EDD_TAXAMOEDDINTEGRATION_NAME.'-'. EDD_TAXAMOEDDINTEGRATION_VER.']';
                    
                    $refundtaxamo = new Taxamo( $private_key );
                    $resp = $refundtaxamo->createRefund( $transaction_key, $taxamo_body_array );
                }
            }
        }

        /**
         * Retrieve the results from the Taxamo api's calculateTax lookup
         * 
         * @param string  $countrycode 2 Letter Country Code
         * @return array
         */
        public function get_api_response_calculate_tax( $countrycode = "" ) {

            $cachedcountrycode = "";

            if ( isset( $this->api_responses['countrycode'] ) ) {
                $cachedcountrycode = $this->api_responses['countrycode'];
            }

            if ( !$countrycode ) {
                $address = edd_get_customer_address();

                if ( isset( $address['country'] ) && !empty( $address['country'] ) && "" !== $address['country'] ) {
                    $countrycode = $address['country'];
                } else {
                    $ipcc = taxedd_get_country_code();
                    $countrycode = $ipcc->country_code;
                }
            }

            // Check if the country code is present, and the same, if so, try to get the cached tax.
            if ( $cachedcountrycode == $countrycode ) {
                if ( $this->get_cached_api_response( 'calculate_tax' ) !== false ) { 

                    return $this->get_cached_api_response( 'calculate_tax' );
                }
            }

            global $edd_options;
            $private_key = $edd_options['taxedd_private_token'];

            try {
                $apiclient = new APIClient( $private_key, 'https://api.taxamo.com' );
                $apiclient->sourceId = '['.EDD_TAXAMOEDDINTEGRATION_DOMAIN.'-'.EDD_TAXAMOEDDINTEGRATION_NAME.'-'. EDD_TAXAMOEDDINTEGRATION_VER.']';
                $taxtaxamo = new Taxamo( $apiclient );

                $cart_items = edd_get_cart_content_details();

                $transaction = new Input_transaction();
                $transaction->currency_code = edd_get_currency();
                $transaction->buyer_ip = $_SERVER['REMOTE_ADDR'];
                $transaction->billing_country_code = $countrycode;
                $transactionarray = array();
                $customid = "";
                $transaction->force_country_code = $countrycode;

                if ( !empty( $cart_items ) ) {
                    foreach ( $cart_items as $cart_item ) {

                        $customid++;
                        $transaction_line = new Input_transaction_line();

                        if ( "yes" == $edd_options['checkout_include_tax'] ) { 
                            $transaction_line->total_amount = $cart_item['price'];
                        } else {
                            $transaction_line->amount = $cart_item['price'];
                        }

                        $transaction_line->custom_id = $cart_item['name'] . $customid;
                        $transaction_line->description = $cart_item['name'];
                        array_push( $transactionarray, $transaction_line );

                    }
                }

                $transaction->transaction_lines = $transactionarray;

                $resp = $taxtaxamo->calculateTax( array( 'transaction' => $transaction ) );

                $this->api_responses['calculate_tax'] = $resp->transaction->tax_amount;
                $this->api_responses['countrycode'] = $countrycode;

                foreach ($resp->transaction->transaction_lines as $transaction_line_tax_rate ) {
                    $tax_rate_array[] = $transaction_line_tax_rate->tax_rate;
                }

                $this->api_responses['tax_rate'] = $tax_rate_array; 

                return $this->api_responses['calculate_tax'];

            } catch ( exception $e ) {

                return "";
            }
        }


        /**
         * Retrieve the results from the Taxamo api's calculateTax lookup
         * 
         * @param string  $countrycode 2 Letter Country Code
         * @return array
         */
        public function get_api_response_calculate_tax_rate( $countrycode = "" ) {

            $cachedcountrycode = "";

            if ( isset( $this->api_responses['countrycode'] ) ) {
                $cachedcountrycode = $this->api_responses['countrycode'];
            }

            if ( !$countrycode ) {
                $address = edd_get_customer_address();

                if ( isset( $address['country'] ) && !empty( $address['country'] ) && "" !== $address['country'] ) {
                    $countrycode = $address['country'];
                } else {
                    $ipcc = taxedd_get_country_code();
                    $countrycode = $ipcc->country_code;
                }
            }

            // Check if the country code is present, and the same, if so, try to get the cached tax.
            if ( $cachedcountrycode == $countrycode ) {

                if ( $this->get_cached_api_response( 'tax_rate' ) !== false ) { 

                    return $this->get_cached_api_response( 'tax_rate' );
                }
            }

            global $edd_options;
            $private_key = $edd_options['taxedd_private_token'];

            try {

                $apiclient = new APIClient( $private_key, 'https://api.taxamo.com' );
                $apiclient->sourceId = '['.EDD_TAXAMOEDDINTEGRATION_DOMAIN.'-'.EDD_TAXAMOEDDINTEGRATION_NAME.'-'. EDD_TAXAMOEDDINTEGRATION_VER.']';
                $taxtaxamo = new Taxamo( $apiclient );

                $cart_items = edd_get_cart_content_details();

                $transaction = new Input_transaction();
                $transaction->currency_code = edd_get_currency();
                $transaction->buyer_ip = $_SERVER['REMOTE_ADDR'];
                $transaction->billing_country_code = $countrycode;
                $transactionarray = array();
                $customid = "";
                $transaction->force_country_code = $countrycode;

                if ( !empty( $cart_items ) ) {
                    foreach ( $cart_items as $cart_item ) {

                        $customid++;
                        $transaction_line = new Input_transaction_line();

                        if ( "yes" == $edd_options['checkout_include_tax'] ) { 
                            $transaction_line->total_amount = $cart_item['price'];
                        } else {
                            $transaction_line->amount = $cart_item['price'];
                        }

                        $transaction_line->custom_id = $cart_item['name'] . $customid;
                        $transaction_line->description = $cart_item['name'];
                        array_push( $transactionarray, $transaction_line );

                    }
                }

                $transaction->transaction_lines = $transactionarray;

                $resp = $taxtaxamo->calculateTax( array( 'transaction' => $transaction ) );

                $this->api_responses['calculate_tax'] = $resp->transaction->tax_amount;
                $this->api_responses['countrycode'] = $countrycode;

                foreach ($resp->transaction->transaction_lines as $transaction_line_tax_rate ) {
                    $tax_rate_array[] = $transaction_line_tax_rate->tax_rate;
                }

                $this->api_responses['tax_rate'] = $tax_rate_array;

                return $this->api_responses['tax_rate'];

            } catch ( exception $e ) {

                return "";
            }
        }


        /**
         * Retrieve the results from the Taxamo api's geoip lookup
         *
         * @return array
         */
        public function get_api_response_geoip( $ip, $private_key ) {

            if ( $this->get_cached_api_response( 'geoip', $ip ) !== false ) {
                return $this->get_cached_api_response( 'geoip', $ip );
            }

            $this->api_responses['geoip'][ $ip ] = wp_remote_get( 'https://api.taxamo.com/api/v1/geoip/'.$ip.'?private_token='. $private_key );

            return $this->api_responses['geoip'][ $ip ];
        }

        /**
         * Retrieve the results from the Taxamo api's VAT lookup
         *
         * @return array
         */
        public function get_api_response_vat_details( $vatnumber, $private_key ) {

            if ( $this->get_cached_api_response( 'vatnumber', $vatnumber ) !== false ) {
                return $this->get_cached_api_response( 'vatnumber', $vatnumber );
            }

            $this->api_responses['vatnumber'][ $vatnumber ] = wp_remote_get( 'https://api.taxamo.com/api/v1/tax/vat_numbers/'.$vatnumber.'/validate?private_token=' . $private_key );

            return $this->api_responses['vatnumber'][ $vatnumber ];
        }

        /**
         * Retrieve a cached call to the Taxamo api if it exists
         *
         * @return array|bool
         */
        public function get_cached_api_response( $type, $id = '' ) {

            if ( empty( $this->api_responses ) || !isset( $this->api_responses[ $type ] ) || ( !empty( $id ) && !isset( $this->api_responses[ $type ][ $id ] ) ) ) {
                return false;

            } else {
                if ( empty( $id ) ) {
                    return $this->api_responses[ $type ];
                } else {
                    return $this->api_responses[ $type ][ $id ];
                }
            }
        }


        /**
         * Check if the country is in the EU
         * @param  string countrycode   the country code is present
         * @return bool                 see if the country code is in the EU
         */
        public function check_if_eu( $countrycode = "" ) {
            $countries = array(
                'AT',
                'BE',
                'BG',
                'CY',
                'CZ',
                'DE',
                'DK',
                'EE',
                'EL',
                'ES',
                'FI',
                'FR',
                'GB',
                'GR',
                'HR',
                'HU',
                'IE',
                'IT',
                'LT',
                'LU',
                'LV',
                'MT',
                'NL',
                'PL',
                'PT',
                'RO',
                'SE',
                'SI',
                'SK',
                );

            foreach ( $countries as $country ) {
                if ( $country == $countrycode ) {
                    return true;
                }
            }

            return false;
        }


        public function add_support_links ( $links ) {
           $mylinks = array(
               '<a href="https://winwar.co.uk/recommends/taxamo/">' . __( 'Sign Up For Taxamo', 'taxamoedd' ) . '</a>',
               '<a href="http://winwar.co.uk/priority-support/?level=3&utm_source=plugins-page&utm_medium=supportlink&utm_campaign=eddtaxamointegration">' . __( 'Get Support', 'taxamoedd' ) . '</a>'
               );
           return array_merge( $links, $mylinks );
       }
    }


    /**
     * Test Functions
     */
    /**
     * Unset all session functions.
     *
     * @return void
     */
    function unset_sessions() {
        wp_session_unset();
    }


    /**
     * The main function responsible for returning the one true EDD_Taxamo_EDD_Integration
     * instance to functions everywhere
     *
     * @since       1.0.0
     * @return      \EDD_Taxamo_EDD_Integration The one true EDD_Taxamo_EDD_Integration
     *
     * @todo        Inclusion of the activation code below isn't mandatory, but
     *              can prevent any number of errors, including fatal errors, in
     *              situations where your extension is activated but EDD is not
     *              present.
     */
    function EDD_Taxamo_EDD_Integration_load() {
        if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
            if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
                require_once 'includes/class.extension-activation.php';
            }

            $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
            $activation = $activation->run();
            return EDD_Taxamo_EDD_Integration::instance();
        } else {
            return EDD_Taxamo_EDD_Integration::instance();
        }
    }

    /**
     * The activation hook is called outside of the singleton because WordPress doesn't
     * register the call from within the class hence, needs to be called outside and the
     * function also needs to be static.
     */
    register_activation_hook( __FILE__, array( 'EDD_Taxamo_EDD_Integration', 'activation' ) );

    add_action( 'plugins_loaded', 'EDD_Taxamo_EDD_Integration_load' );

} // End if class_exists check
