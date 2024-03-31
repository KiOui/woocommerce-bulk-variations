<?php
/**
 * WooCommerce Bulk Variations Core class
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WBV Core class
 *
 * @class WBVCore
 */
if ( ! class_exists( 'WbvCore' ) ) {
	/**
	 * WooCommerce Bulk Variations Core class
	 *
	 * @class WbvCore
	 */
	class WbvCore {

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The single instance of the class
		 *
		 * @var WbvCore|null
		 */
		protected static $_instance = null;

		/**
		 * WooCommerce Bulk Variations Core instance
		 *
		 * Uses the Singleton pattern to load 1 instance of this class at maximum
		 *
		 * @static
		 * @return WbvCore
		 */
		public static function instance(): WbvCore {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			$this->define_constants();
			$this->init_hooks();
			$this->actions_and_filters();
		}

		/**
		 * Initialise WooCommerce Bulk Variations
		 */
		public function init() {
			$this->initialise_localisation();
			do_action( 'wbv_init' );
		}

		/**
		 * Initialise the localisation of the plugin.
		 */
		private function initialise_localisation() {
			load_plugin_textdomain( 'woocommerce-bulk-variations', false, plugin_basename( dirname( WBV_PLUGIN_FILE ) ) . '/languages/' );
		}

		/**
		 * Define constants of the plugin.
		 */
		private function define_constants() {
			$this->define( 'WBV_ABSPATH', dirname( WBV_PLUGIN_FILE ) . '/' );
			$this->define( 'WBV_VERSION', $this->version );
			$this->define( 'WBV_FULLNAME', 'woocommerce-bulk-variations' );
		}

		/**
		 * Define if not already set
		 *
		 * @param string $name The name of the variable that should be defined.
		 * @param string $value The value that the variable should be set to.
		 */
		private static function define( string $name, string $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Initialise activation and deactivation hooks.
		 */
		private function init_hooks() {
			register_activation_hook( WBV_PLUGIN_FILE, array( $this, 'activation' ) );
			register_deactivation_hook( WBV_PLUGIN_FILE, array( $this, 'deactivation' ) );
		}

		/**
		 * Activation hook call.
		 */
		public function activation() {
		}

		/**
		 * Deactivation hook call.
		 */
		public function deactivation() {
		}

		/**
		 * Add pluggable support to functions
		 */
		public function pluggable() {
			include_once WBV_ABSPATH . 'includes/wbv-functions.php';
		}

		/**
		 * Add actions and filters.
		 */
		private function actions_and_filters() {
			add_action( 'after_setup_theme', array( $this, 'pluggable' ) );
			add_action( 'init', array( $this, 'init' ) );
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'render_notice' ), 99 );
				add_action( 'wbv_init', array( $this, 'add_meta_box_support' ) );
			} else {
				include_once WBV_ABSPATH . 'includes/WbvBulkForm.php';
				$bulk_form_class = new WbvBulkForm();
				$bulk_form_class->actions_and_filters();
				$GLOBALS['WbvBulkForm'] = $bulk_form_class;
				add_action( 'wp_loaded', array( $this, 'process_matrix_submission' ), 99 );
			}
		}

		/**
		 * Check if the admin notice should be rendered.
		 *
		 * @param WP_Post $post The post to check.
		 *
		 * @return bool whether to render the admin notice
		 */
		private function should_render_notice( WP_Post $post ): bool {
			return wbv_is_variable_product( $post ) && wbv_bulk_form_enabled( $post ) && ! wbv_row_column_set( $post );
		}

		/**
		 * Render an admin notice when necessary.
		 */
		public function render_notice() {
			global $post;
			if ( ! is_null( $post ) && $this->should_render_notice( $post ) ) {
				?>
				<div class="error notice">
					<p><?php echo esc_html( __( 'This product has a configuration error in the WooCommerce Bulk Variation Form settings. The WooCommerce Bulk Variation Form will not get rendered on the product page.', 'woocommerce-bulk-variations' ) ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Add Bulk Form meta boxes to products.
		 */
		public function add_meta_box_support() {
			include_once WBV_ABSPATH . 'includes/metaboxes/Metabox.php';
			new Metabox(
				'wbv_metabox',
				array(
					array(
						'label' => __( 'Bulk variation form', 'woocommerce-bulk-variations' ),
						'id'    => 'wbv_enable_bulk_form',
						'type'  => 'checkbox',
					),
					array(
						'label' => __( 'Singular variation form', 'woocommerce-bulk-variations' ),
						'id'    => 'wbv_enable_singular_form',
						'type'  => 'checkbox',
					),
					array(
						'label' => __( 'Bulk variation form column', 'woocommerce-bulk-variations' ),
						'id'    => 'wbv_bulk_form_column',
						'type'  => 'select',
						'options' => 'wbv_get_bulk_variation_options',
					),
					array(
						'label' => __( 'Bulk variation form row', 'woocommerce-bulk-variations' ),
						'id'    => 'wbv_bulk_form_row',
						'type'  => 'select',
						'options' => 'wbv_get_bulk_variation_options',
					),
				),
				'product',
				__( 'Bulk variation form Settings', 'woocommerce-bulk-variations' ),
				'side'
			);
		}

		/**
		 * Output success message.
		 */
		public function render_add_to_cart_notice( $count ) {
			if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
				$return_to = ( wp_get_referer() ) ? wp_get_referer() : home_url();
				$message   = sprintf( '<a href="%s" class="button">%s</a> %s', $return_to, __( 'Continue Shopping &rarr;', 'woocommerce-bulk-variations' ), sprintf( __( '%s products successfully added to your cart.', 'woocommerce-bulk-variations' ), $count ) );
			} else {
				$message = sprintf( '<a href="%s" class="button">%s</a> %s', get_permalink( wc_get_page_id( 'cart' ) ), __( 'View Cart &rarr;', 'woocommerce-bulk-variations' ), sprintf( __( '%s products successfully added to your cart.', 'woocommerce-bulk-variations' ), $count ) );
			}
			wc_add_notice( apply_filters( 'woocommerce_bv_add_to_cart_message', $message ) );
		}

		/**
		 * Process matrix submission and add products to cart.
		 */
		public function process_matrix_submission() {
			if ( ! isset( $_POST['woocommerce-bulk-variations-submission'] ) || ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['order_info'] ) || ! isset( $_POST['product_id'] ) ) {
				// One of the needed POST variables is not set.
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wbv_submit_bulk_form' ) ) {
				return;
			}

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- We sanitize below.
			$items = wp_unslash( $_POST['order_info'] );
			if ( ! is_array( $items ) ) {
				return;
			}

			// Sanitize order info.
			$sanitized_items = array();
			foreach ( $items as $item ) {
				if ( key_exists( 'quantity', $item ) && key_exists( 'variation_id', $item ) ) {
					$quantity = intval( $item['quantity'] );
					$variation_id = intval( $item['variation_id'] );
					$sanitized_items[ $variation_id ] = $quantity;
				}
			}

			// Sanitize product.
			$product_id = intval( wp_unslash( $_POST['product_id'] ) );
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return;
			}

			$amount_added = 0;

			// Actually add items to cart.
			foreach ( $sanitized_items as $variation_id => $quantity ) {
				$product_variation = wc_get_product( $variation_id );
				if ( 0 < $quantity && $product_variation ) {
					if ( apply_filters( 'woocommerce_add_to_cart_validation', true, $product->get_id(), $quantity, $product_variation->get_id() ) ) {
						try {
							if ( WC()->cart->add_to_cart( $product->get_id(), $quantity, $product_variation->get_id() ) ) {
								$amount_added = $amount_added + $quantity;
							}
						} catch ( Exception $e ) {
							wc_add_notice( sprintf( __( 'Failed to add %s to cart.', 'woocommerce-bulk-variations' ), $product_variation->get_name() ) );
						}
					}
				}
			}

			if ( 0 === $amount_added ) {
				wc_add_notice( __( 'Nothing was added to cart because no quantities were entered.', 'woocommerce-bulk-variations' ), 'error' );
			} else {
				$this->render_add_to_cart_notice( $amount_added );
			}

			// Redirect if necessary.
			$url = apply_filters( 'woocommerce_add_to_cart_redirect', false );
			if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			} elseif ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
	}
}
