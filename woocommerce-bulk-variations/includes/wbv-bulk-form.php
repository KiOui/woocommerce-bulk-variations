<?php

if (!class_exists("WBVBulkForm")) {
    class WBVBulkForm {

		function add_scripts_and_styles() {
			wp_enqueue_script( 'woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/js/bulk-variations.js', array( 'jquery' ) );
            wp_enqueue_style('woocommerce-bulk-variations', WBV_PLUGIN_URI . 'assets/css/variable-grid.css');
		}

        public function actions_and_filters() {
	        add_action('woocommerce_before_single_product', array($this, 'render_bulk_form'), 99);
        }

	    public function before_add_to_cart_form() {
		    global $post;
		    if (wbv_has_bulk_variation_form($post)) {
                ?>
                <input class="button btn-bulk" type="button" value="<?php echo __( 'Bulk Order Form', 'woocommerce-bulk-variations' ); ?>"  />
                <?php
		    }
	    }

        public function render_bulk_form() {
			global $post;
            if (wbv_has_bulk_variation_form($post)) {
	            if (!wbv_single_product_form_enabled($post)) {
		            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		            add_action( 'woocommerce_product_meta_start', array($this, 'before_add_to_cart_form'));
	            }
                else {
	                add_action( 'woocommerce_before_add_to_cart_form', array($this, 'before_add_to_cart_form'));
                }
				$this->add_scripts_and_styles();
                wc_get_template( 'variable-grid.php', array(), WC_TEMPLATE_PATH . '/single-product/', WBV_ABSPATH . 'templates/single-product/' );
            }
        }
    }
}