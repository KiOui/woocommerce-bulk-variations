<?php
/**
 * WooCommerce Bulk Variations Form class
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once WBV_ABSPATH . 'includes/WbvMatrix.php';
include_once WBV_ABSPATH . 'includes/WbvMatrixException.php';

if ( ! class_exists( 'WbvBulkForm' ) ) {
	/**
	 * WooCommerce Bulk Variations Bulk Form
	 *
	 * @class WbvBulkForm
	 */
	class WbvBulkForm {

		/**
		 * Enqueue scripts and styles.
		 */
		public function add_scripts_and_styles() {
			wp_enqueue_script( 'woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/js/bulk-variations.js', array( 'jquery' ), null, true );
			wp_enqueue_style( 'woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/css/variable-grid.css' );
		}

		/**
		 * Add actions and filters.
		 */
		public function actions_and_filters() {
			add_action( 'woocommerce_before_single_product', array( $this, 'render_bulk_form' ), 99 );
		}

		/**
		 * Render the bulk form button.
		 *
		 * This should only be added for products that have the bulk form configured.
		 */
		public function before_add_to_cart_form() {
			?>
			<input id="wbv-bulk-button" class="button btn_bulk" type="button" value="<?php echo esc_attr( __( 'Bulk Order Form', 'woocommerce-bulk-variations' ) ); ?>"  />
			<?php
		}

		/**
		 * Show an error message that the Bulk for is misconfigured and therefor not shown.
		 *
		 * @param string $error The error message to show.
		 * @return void
		 */
		public function show_error_message_form_misconfigured( string $error ) {
			global $post;
			if ( current_user_can( 'edit_post', $post ) ) {
				echo '<div class="notice notice-error" style="color: red"><p>' . esc_html( $error ) . '</p></div>';
			}
		}

		/**
		 * Render the bulk form.
		 */
		public function render_bulk_form() {
			global $post;
			global $product;
			if ( wbv_has_bulk_variation_form( $post ) && $product->is_type( 'variable' ) ) {
				// Test if settings are configured correctly.
				$row_attribute = get_post_meta( $product->get_id(), 'wbv_bulk_form_row', true );
				$column_attribute = get_post_meta( $product->get_id(), 'wbv_bulk_form_column', true );

				if ( ! $row_attribute ) {
					add_action(
						'woocommerce_product_meta_start',
						function () {
							$error = __( 'WooCommerce Bulk Variations is enabled for this product but no (valid) row attribute is set. Please review the configuration in the product settings.', 'woocommerce-bulk-variations' );
							$this->show_error_message_form_misconfigured( $error );
						}
					);
					return;
				}

				if ( ! $column_attribute ) {
					add_action(
						'woocommerce_product_meta_start',
						function () {
							$error = __( 'WooCommerce Bulk Variations is enabled for this product but no (valid) column attribute is set. Please review the configuration in the product settings.', 'woocommerce-bulk-variations' );
							$this->show_error_message_form_misconfigured( $error );
						}
					);
					return;
				}

				if ( $row_attribute === $column_attribute ) {
					add_action(
						'woocommerce_product_meta_start',
						function () {
							$error = __( 'WooCommerce Bulk Variations is enabled for this product but the set column and row attribute is the same. Please review the configuration in the product settings.', 'woocommerce-bulk-variations' );
							$this->show_error_message_form_misconfigured( $error );
						}
					);
					return;
				}

				try {
					// We have checked the product type above so this is safe.
					$GLOBALS['WbvBulkMatrix'] = new WbvMatrix( $product, $row_attribute, $column_attribute );
				} catch ( WbvMatrixException $e ) {
					add_action(
						'woocommerce_product_meta_start',
						function () use ( $e ) {
							$error = sprintf( __( 'WooCommerce Bulk Variations is enabled for this product but it failed to generate a matrix: %s', 'woocommerce-bulk-variations' ), e );
							$this->show_error_message_form_misconfigured( $error );
						}
					);
					return;
				}

				if ( ! wbv_single_product_form_enabled( $post ) ) {
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				}

				add_action( 'woocommerce_product_meta_start', array( $this, 'before_add_to_cart_form' ) );
				wc_get_template( 'variable-grid.php', array(), WC_TEMPLATE_PATH . '/single-product/', WBV_ABSPATH . 'templates/single-product/' );

				$this->add_scripts_and_styles();
			}
		}
	}
}
