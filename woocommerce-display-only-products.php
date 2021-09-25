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
 * Text Domain: woocommerce-display-only-products
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
         * Main Extension Instance.
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Adding the Display Only checkbox to the product page for admins.
         */
        function wdop_add_display_only_checkbox() {
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

        /*
         * Save our Display Only product field
         */
        function wdop_save_display_only_checkbox( $post_id ){
            $displayonly_checkbox = isset( $_POST['_wdop_display_only'] ) ? 'yes' : 'no';
            update_post_meta( $post_id, '_wdop_display_only', $displayonly_checkbox );
        }

        function wdop_save_display_only_message() {

            echo '<p><a href="#" class="button">Call Us To Enquire!</a></p>';
            echo '<p><em>Please contact us to discuss purchasing this item<em></p>';
            // TODO: make this configurable and add link to hook into contact form which attaches product
        }

        /**
         * Hide the Add to Cart button on the single product page
         */
        public function wdop_single_product_hide_cart_buttons() {
            global $product;

            $display_only = $product->get_meta('_wdop_display_only');

            if (!empty($display_only)) {
                $display_only = wc_string_to_bool($display_only);
            } else {
                $display_only = false;
            }

            if($display_only) {
                if ($product->is_type('variable')) {
                    // Remove Add to Cart on Variation
                    remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
                } else {
                    // Remove Add to Cart
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
                }
                add_action( 'woocommerce_single_product_summary', array( $this, 'wdop_save_display_only_message' ), 35 );
            }
        }

        /**
         * Function for getting everything set up and ready to run.
         */
        private function init() {
            add_action( 'woocommerce_product_options_pricing', array( $this, 'wdop_add_display_only_checkbox' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'wdop_save_display_only_checkbox') );
            add_action( 'woocommerce_single_product_summary', array ( $this, 'wdop_single_product_hide_cart_buttons' ) );
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