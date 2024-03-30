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
			wp_enqueue_script( 'woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/js/bulk-variations.js', array( 'jquery' ) );
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
			<input class="button btn_bulk" type="button" value="<?php echo esc_attr( __( 'Bulk Order Form', 'woocommerce-bulk-variations' ) ); ?>"  />
			<?php
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
					// TODO: Show error message.
					return;
				}

				if ( ! $column_attribute ) {
					// TODO: Show error message.
					return;
				}

				try {
					// We have checked the product type above so this is safe.
					$GLOBALS['WbvBulkMatrix'] = new WbvMatrix( $product, $row_attribute, $column_attribute );
				} catch ( WbvMatrixException $e ) {
					// TODO: handle exception.
					return;
				}

				$this->add_scripts_and_styles();

				if ( ! wbv_single_product_form_enabled( $post ) ) {
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
				}

				add_action( 'woocommerce_product_meta_start', array( $this, 'before_add_to_cart_form' ) );
				wc_get_template( 'variable-grid.php', array(), WC_TEMPLATE_PATH . '/single-product/', WBV_ABSPATH . 'templates/single-product/' );
			}
		}
	}
}
