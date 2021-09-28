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
 * Text Domain: woo-display-only-products
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
         * Create plugin options page.
         */
        public function add_submenu_page() {
            add_submenu_page(
                'woocommerce',
                _x( 'Display Only Settings', 'page title', 'woo-display-only-products' ),
                _x( 'Display Only Products', 'menu title', 'woo-display-only-products' ),
                'manage_woocommerce',
                'woo-display-only-products',
                array( $this, 'wdop_options_page' )
            );
        }

        /**
         * Render and display plugin options page.
         */
        public function wdop_options_page() {
            $this->options = (array) get_option( 'woo_display_only_products_options' );
            ?>
                <form method="POST" action="options.php">
                    <h1>
                        <?php esc_html_e( 'WooCommerce Display Only Products', 'woo-display-only-products' ); ?>
                    </h1>
                    <?php
                    // Get settings fields.
                    settings_fields( 'woo_display_only_products_settings_fields' );
                    do_settings_sections( 'woo_display_only_products_settings_sections' );
                    submit_button();
                    ?>
                </form>
            <?php
        }

        /**
         * Register plugin settings.
         */
        public function register_settings() {
            // Register a setting and its data.
            register_setting( 'woo_display_only_products_settings_fields', 'woo_display_only_products_options' );

            // Add a new section to a settings page.
            add_settings_section(
                'woo_display_only_products_settings_section',
                '',
                '',
                'woo_display_only_products_settings_sections'
            );

            add_settings_field(
                'btn_txt',
                esc_html_x( 'Button Text', 'settings field label', 'woo-display-only-products' ),
                array( $this, 'btn_txt_callback' ),
                'woo_display_only_products_settings_sections',
                'woo_display_only_products_settings_section'
            );

            add_settings_field(
                'btn_url',
                esc_html_x( 'Button URL', 'settings field label', 'woo-display-only-products' ),
                array( $this, 'btn_url_callback' ),
                'woo_display_only_products_settings_sections',
                'woo_display_only_products_settings_section'
            );

            add_settings_field(
                'extra_info',
                esc_html_x( 'Extra Info', 'settings field label', 'woo-display-only-products' ),
                array( $this, 'extra_info_callback' ),
                'woo_display_only_products_settings_sections',
                'woo_display_only_products_settings_section'
            );
        }

        /**
         * Display Only Button Text.
         */
        public function btn_txt_callback() {
            $placeholder = _x( 'Call Us To Enquire', 'settings field placeholder', 'woo-display-only-products' );
            $value       = isset( $this->options['btn_txt'] ) ? $this->options['btn_txt'] : null;
            printf(
                '<input 
                    type="text" 
                    class="regular-text" 
                    name="woo_display_only_products_options[btn_txt]" 
                    id="btn_txt" 
                    placeholder="%s" 
                    value="%s" 
                />',
                esc_attr( $placeholder ),
                esc_html( $value )
            );
        }

        /**
         * Display Only Button URL.
         */
        public function btn_url_callback() {
            $placeholder = _x( 'tel:+4411112223333', 'settings field placeholder', 'woo-display-only-products' );
            $value       = isset( $this->options['btn_url'] ) ? $this->options['btn_url'] : null;
            printf(
                '<input 
                    type="url" 
                    class="regular-text" 
                    name="woo_display_only_products_options[btn_url]" 
                    id="btn_url" 
                    placeholder="%s" 
                    value="%s" 
                />',
                esc_attr( $placeholder ),
                esc_url( $value )
            );
        }

        /**
         * Message to be displayed below custom button (optional).
         */
        public function extra_info_callback() {
            $placeholder = _x( 'Please contact us to discuss purchasing this item.', 'settings field placeholder', 'woo-display-only-products' );
            $value       = isset( $this->options['extra_info'] ) ? esc_attr( $this->options['extra_info'] ) : null;
            printf(
                '<textarea 
                    class="large-text" 
                    rows="5" 
                    name="woo_display_only_products_options[extra_info]" 
                    id="extra_info" 
                    placeholder="%s"
                >%s</textarea>',
                esc_attr( $placeholder ),
                esc_html( $value )
            );
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
         * Save our Display Only product field.
         */
        function wdop_save_display_only_checkbox( $post_id ){
            $displayonly_checkbox = isset( $_POST['_wdop_display_only'] ) ? 'yes' : 'no';
            update_post_meta( $post_id, '_wdop_display_only', $displayonly_checkbox );
        }

        /*
         * Button and message for display only items.
         */
        function wdop_template_display_only_message() {
            $options = (array) get_option( 'woo_display_only_products_options' );
            $url = isset( $options['btn_url'] ) ? esc_attr( $options['btn_url'] ) : '#';
            if ( isset( $options['btn_txt'] ) && !empty( $options['btn_txt'] ) ) {
                echo '<p><a href="' . $url . '" class="button">' . esc_attr( $options['btn_txt'] ) . '</a></p>';
            }
            if ( isset( $options['extra_info'] ) ) {
                echo '<p><em>' . esc_attr( $options['extra_info'] ) . '<em></p>';
            }
        }
        /**
         * Function to find if a passed product is display only. Returns bool.
         */
        protected function wdop_is_display_only( $product ) {
            $display_only = $product->get_meta('_wdop_display_only');

            if ( !empty( $display_only ) ) {
                $display_only = wc_string_to_bool($display_only);
            } else {
                $display_only = false;
            }
            return $display_only;
        }

        /**
         * Hide the Add to Cart button on the single product page.
         */
        public function wdop_single_product_hide_cart_buttons() {
            global $product;

            if ( $this->wdop_is_display_only( $product ) ) {
                if ( $product->is_type('variable') ) {
                    // Remove Add to Cart on Variation
                    remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
                } else {
                    // Remove Add to Cart
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
                }
                add_action( 'woocommerce_single_product_summary', array( $this, 'wdop_template_display_only_message' ), 35 );
            }
        }

        /**
         * Hide the Add to Cart button on the loop page.
         */
        public function wdop_shop_loop_product_hide_cart_buttons( $add_to_cart_link, $product ) {
            if ( $this->wdop_is_display_only( $product ) ) {
                $link = $product->get_permalink();
                $add_to_cart_link = do_shortcode('<a href="'.$link.'" class="button">View Product</a>');
            }
            return $add_to_cart_link;
        }


        public function wdop_is_purchasable($notused, $product) {
            $display_only = $this->wdop_is_display_only($product);
            if ($display_only) {
                return false;
            }
            return true;
        }


        /**
         * Function for getting everything set up and ready to run.
         */
        private function init() {
            add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 999 );
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_filter( 'woocommerce_is_purchasable', array ( $this, 'wdop_is_purchasable'), 99, 2);
            add_action( 'woocommerce_product_options_pricing', array( $this, 'wdop_add_display_only_checkbox' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'wdop_save_display_only_checkbox') );
            add_action( 'woocommerce_single_product_summary', array ( $this, 'wdop_single_product_hide_cart_buttons' ) );
            add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'wdop_shop_loop_product_hide_cart_buttons'), 10, 2);
        }
    }
endif;

/**
 * Function for delaying initialization of the extension until after WooComerce is loaded.
 */
function woo_display_only_products_initialize() {

    // This is also a great place to check for the existence of the WooCommerce class.
    if ( ! class_exists( 'WooCommerce' ) ) {
        // TODO: Add a WordPress admin notice if WooCommerce isn't installed.
        return;
    }

    $GLOBALS['woo_display_only_products'] = Woocommerce_Display_Only_Products::instance();
}
add_action( 'plugins_loaded', 'woo_display_only_products_initialize', 10 );