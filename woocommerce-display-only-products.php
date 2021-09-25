<?php
/**
 * Plugin Name: WooCommerce Display Only Products
 * Plugin URI: http://s-oh.co.uk
 * Description: A simple plugin which allows selected products on the site to be display only with all purchase features
 * disabled.
 * Version: 1.0.0
 * Author: Stephen O'Hara
 * Author URI: http://s-oh.co.uk
 * Developer: Stephen O'Hara
 * Developer URI: http://s-oh.co.uk
 * Text Domain: my-extension
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woocommerce_Display_Only_Products' ) ) :
    /**
     * Woocommerce Display Only Products core class
     */
    class Woocommerce_Display_Only_Products {

        /**
         * The single instance of the class.
         */
        protected static $_instance = null;

        /**
         * Constructor.
         */
        protected function __construct() {
            $this->init();
        }

        /**
         * Adding the Display Only checkbox to the product page for admins.
         */
        function wdop_add_admin_checkbox() {
            global $product_object;
            // Display only.
            woocommerce_wp_checkbox(
                array(
                    'id'          => '_wdop_display_only',
                    'label'       => __( 'Display Only', 'woocommerce' ),
                    'description' => __( 'Check this box if you do not want this item to be available for purchase on the website.', 'woocommerce' ),
                    'value'       => wc_bool_to_string( $product_object->get_meta('_wdop_display_only') ),
                )
            );
        }

        /**
         * Function for getting everything set up and ready to run.
         */
        private function init() {
            add_action( 'woocommerce_product_options_pricing', 'wdop_add_admin_checkbox' );
        }
    }
endif;

/**
 * Function for delaying initialization of the extension until after WooComerce is loaded.
 */
function woocommerce_display_only_products_initialize() {

    // This is also a great place to check for the existence of the WooCommerce class
    if ( ! class_exists( 'WooCommerce' ) ) {
        // TODO: Add a WordPress admin notice if WooCommerce isn't installed.
        return;
    }

    $GLOBALS['woocommerce_display_only_products'] = Woocommerce_Display_Only_Products::instance();
}
add_action( 'plugins_loaded', 'woocommerce_display_only_products_initialize', 10 );